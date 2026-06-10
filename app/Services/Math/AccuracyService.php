<?php

namespace App\Services\Math;

use App\Models\Forecast;
use App\Models\ForecastAccuracy;
use App\Models\SystemMetric;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AccuracyService
{
    /**
     * Оценка точности прогнозов
     */
    public function evaluateForecasts(int $hoursBack = 24): array
    {
        $forecasts = Forecast::where('forecasted_at', '<=', now())
            ->where('created_at', '>=', now()->subHours($hoursBack))
            ->whereDoesntHave('accuracyRecords')
            ->get();

        $results = [];

        foreach ($forecasts as $forecast) {
            // ищем фактическое значение в момент прогноза
            $actualMetric = SystemMetric::where('recorded_at', '>=', $forecast->forecasted_at->subMinutes(5))
                ->where('recorded_at', '<=', $forecast->forecasted_at->addMinutes(5))
                ->first();

            if (!$actualMetric) {
                continue;
            }

            $actualValue = $actualMetric->{$forecast->metric_type};
            $forecastValue = $forecast->forecast_value;

            // расчет ошибок
            $absoluteError = abs($actualValue - $forecastValue);
            $percentageError = $actualValue != 0 ? ($absoluteError / $actualValue) * 100 : 100;
            $squaredError = ($actualValue - $forecastValue) ** 2;

            // накопленная статистика по методу и метрике
            $historicalAccuracy = ForecastAccuracy::whereHas('forecast', function($q) use ($forecast) {
                $q->where('metric_type', $forecast->metric_type)
                  ->where('forecast_method', $forecast->forecast_method);
            })->get();

            $metricsSummary = $this->calculateAggregatedMetrics($historicalAccuracy);

            $accuracyRecord = ForecastAccuracy::create([
                'forecast_id' => $forecast->id,
                'actual_value' => $actualValue,
                'absolute_error' => $absoluteError,
                'percentage_error' => $percentageError,
                'squared_error' => $squaredError,
                'evaluation_method' => 'mae_mape_rmse',
                'metrics_summary' => $metricsSummary,
                'evaluated_at' => now(),
            ]);

            $results[] = [
                'forecast_id' => $forecast->id,
                'metric' => $forecast->metric_type,
                'method' => $forecast->forecast_method,
                'forecast' => $forecastValue,
                'actual' => $actualValue,
                'mae' => $absoluteError,
                'mape' => $percentageError,
                'rmse' => sqrt($squaredError),
            ];

            // логирование при плохой точности (>30% ошибки)
            if ($percentageError > 30) {
                Log::warning('Низкая точность прогноза', [
                    'forecast_id' => $forecast->id,
                    'mape' => round($percentageError, 2),
                    'metric' => $forecast->metric_type,
                    'method' => $forecast->forecast_method,
                ]);
            }
        }

        return $results;
    }

    /**
     * Получить лучший метод прогнозирования для каждой метрики
     */
    public function getBestMethodPerMetric(): Collection
    {
        $metrics = ['cpu_usage', 'memory_usage', 'disk_usage', 'active_connections'];
        $result = [];

        foreach ($metrics as $metric) {
            $best = ForecastAccuracy::selectRaw('forecasts.forecast_method, AVG(percentage_error) as avg_mape')
                ->join('forecasts', 'forecast_accuracy.forecast_id', '=', 'forecasts.id')
                ->where('forecasts.metric_type', $metric)
                ->where('forecast_accuracy.created_at', '>=', now()->subDays(7))
                ->groupBy('forecasts.forecast_method')
                ->orderBy('avg_mape', 'asc')
                ->first();

            if ($best) {
                $result[$metric] = [
                    'method' => $best->forecast_method,
                    'avg_mape' => round($best->avg_mape, 2),
                ];
            }
        }

        return collect($result);
    }

    /**
     * Расчет агрегированных метрик точности
     */
    private function calculateAggregatedMetrics(Collection $records): array
    {
        if ($records->isEmpty()) {
            return [
                'count' => 0,
                'mean_mae' => null,
                'mean_mape' => null,
                'mean_rmse' => null,
                'trend' => 'insufficient_data',
            ];
        }

        $mae = $records->avg('absolute_error');
        $mape = $records->avg('percentage_error');
        $rmse = sqrt($records->avg('squared_error'));

        // определение тренда точности (улучшается/ухудшается)
        $sorted = $records->sortBy('evaluated_at');
        $recent = $sorted->slice(-5)->avg('percentage_error');
        $older = $sorted->slice(0, 5)->avg('percentage_error');
        
        $trend = 'stable';
        if ($recent && $older) {
            if ($recent < $older * 0.95) $trend = 'improving';
            if ($recent > $older * 1.05) $trend = 'degrading';
        }

        return [
            'count' => $records->count(),
            'mean_mae' => round($mae, 2),
            'mean_mape' => round($mape, 2),
            'mean_rmse' => round($rmse, 2),
            'trend' => $trend,
            'last_evaluated' => $records->max('evaluated_at'),
        ];
    }

    /**
     * Расчет R² (коэффициент детерминации)
     */
    public function calculateR2Score(string $metricType, string $method = null): float
    {
        $query = Forecast::where('metric_type', $metricType)
            ->whereHas('accuracyRecords');

        if ($method) {
            $query->where('forecast_method', $method);
        }

        $forecasts = $query->with('accuracyRecords')->get();

        if ($forecasts->count() < 2) {
            return 0;
        }

        $actuals = [];
        $predicteds = [];

        foreach ($forecasts as $forecast) {
            $accuracy = $forecast->getLatestAccuracy();
            if ($accuracy) {
                $actuals[] = $accuracy->actual_value;
                $predicteds[] = $forecast->forecast_value;
            }
        }

        $actualMean = array_sum($actuals) / count($actuals);
        $ssTot = array_sum(array_map(function($a) use ($actualMean) {
            return ($a - $actualMean) ** 2;
        }, $actuals));

        $ssRes = 0;
        for ($i = 0; $i < count($actuals); $i++) {
            $ssRes += ($actuals[$i] - $predicteds[$i]) ** 2;
        }

        return $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;
    }
}
