<?php

namespace App\Services\Math;

use App\Models\SystemMetric;
use App\Models\Forecast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ForecastService
{
    /**
     * Линейная регрессия
     */
    public function linearRegression(Collection $data, int $horizonMinutes = 60): ?array
    {
        if ($data->count() < 2) {
            return null;
        }

        $n = $data->count();
        $x = range(1, $n);
        $y = $data->pluck('value')->toArray();

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        // slope (наклон)
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        // intercept (пересечение)
        $intercept = ($sumY - $slope * $sumX) / $n;

        // прогноз на следующий период
        $nextX = $n + ($horizonMinutes / 5); // предполагаем, что данные каждые 5 минут
        $forecastValue = $slope * $nextX + $intercept;

        // стандартная ошибка для доверительного интервала
        $residuals = [];
        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $residuals[] = $y[$i] - $predicted;
        }
        
        $stdError = sqrt(array_sum(array_map(function($r) { return $r * $r; }, $residuals)) / ($n - 2));
        $marginError = 1.96 * $stdError; // 95% доверительный интервал

        return [
            'value' => max(0, $forecastValue),
            'lower' => max(0, $forecastValue - $marginError),
            'upper' => $forecastValue + $marginError,
            'slope' => $slope,
            'intercept' => $intercept,
            'r2' => $this->calculateR2($y, $x, $slope, $intercept),
        ];
    }

    /**
     * Скользящее среднее
     */
    public function movingAverage(Collection $data, int $window = 6, int $horizonMinutes = 60): ?array
    {
        if ($data->count() < $window) {
            return null;
        }

        $values = $data->pluck('value')->toArray();
        $window = min($window, count($values));
        
        $lastWindow = array_slice($values, -$window);
        $forecastValue = array_sum($lastWindow) / $window;

        // доверительный интервал на основе стандартного отклонения окна
        $stdDev = $this->calculateStdDev($lastWindow);
        $marginError = 1.96 * $stdDev / sqrt($window);

        return [
            'value' => max(0, $forecastValue),
            'lower' => max(0, $forecastValue - $marginError),
            'upper' => $forecastValue + $marginError,
            'window' => $window,
            'stdDev' => $stdDev,
        ];
    }

    /**
     * Экспоненциальное сглаживание
     */
    public function exponentialSmoothing(Collection $data, float $alpha = 0.3, int $horizonMinutes = 60): ?array
    {
        if ($data->isEmpty()) {
            return null;
        }

        $values = $data->pluck('value')->toArray();
        $smoothed = $values[0];
        
        for ($i = 1; $i < count($values); $i++) {
            $smoothed = $alpha * $values[$i] + (1 - $alpha) * $smoothed;
        }

        $forecastValue = $smoothed;

        // ошибка прогноза
        $errors = [];
        $lastSmoothed = $values[0];
        for ($i = 1; $i < count($values); $i++) {
            $predicted = $alpha * $values[$i] + (1 - $alpha) * $lastSmoothed;
            $errors[] = $values[$i] - $predicted;
            $lastSmoothed = $predicted;
        }

        $stdError = $this->calculateStdDev($errors);
        $marginError = 1.96 * $stdError;

        return [
            'value' => max(0, $forecastValue),
            'lower' => max(0, $forecastValue - $marginError),
            'upper' => $forecastValue + $marginError,
            'alpha' => $alpha,
            'mse' => array_sum(array_map(function($e) { return $e * $e; }, $errors)) / count($errors),
        ];
    }

    /**
     * Генерация прогнозов для всех метрик
     */
    public function generateAllForecasts(int $horizonMinutes = 60): void
    {
        $metricTypes = ['cpu_usage', 'memory_usage', 'disk_usage', 'active_connections'];
        $methods = ['linear_regression', 'moving_average', 'exponential_smoothing'];

        foreach ($metricTypes as $metricType) {
            // берем последние 24 точки (2 часа при сборе каждые 5 минут)
            $historicalData = SystemMetric::orderBy('recorded_at', 'desc')
                ->limit(24)
                ->get()
                ->map(function($item) use ($metricType) {
                    return (object) [
                        'value' => $item->{$metricType},
                        'recorded_at' => $item->recorded_at,
                    ];
                })
                ->reverse()
                ->values();

            if ($historicalData->count() < 6) {
                continue;
            }

            foreach ($methods as $method) {
                $forecast = null;
                $metadata = [];

                switch ($method) {
                    case 'linear_regression':
                        $result = $this->linearRegression($historicalData, $horizonMinutes);
                        if ($result) {
                            $forecast = $result['value'];
                            $metadata = ['slope' => $result['slope'], 'intercept' => $result['intercept'], 'r2' => $result['r2']];
                        }
                        break;
                    
                    case 'moving_average':
                        $result = $this->movingAverage($historicalData, 6, $horizonMinutes);
                        if ($result) {
                            $forecast = $result['value'];
                            $metadata = ['window' => $result['window'], 'stdDev' => $result['stdDev']];
                        }
                        break;
                    
                    case 'exponential_smoothing':
                        $result = $this->exponentialSmoothing($historicalData, 0.3, $horizonMinutes);
                        if ($result) {
                            $forecast = $result['value'];
                            $metadata = ['alpha' => $result['alpha'], 'mse' => $result['mse']];
                        }
                        break;
                }

                if ($forecast !== null && $result) {
                    Forecast::create([
                        'metric_type' => $metricType,
                        'forecast_method' => $method,
                        'forecast_value' => $forecast,
                        'lower_bound' => $result['lower'] ?? null,
                        'upper_bound' => $result['upper'] ?? null,
                        'forecasted_at' => now()->addMinutes($horizonMinutes),
                        'horizon_minutes' => $horizonMinutes,
                        'metadata' => $metadata,
                    ]);
                }
            }
        }

        Log::info('Прогнозы сгенерированы', ['horizon' => $horizonMinutes]);
    }

    private function calculateR2(array $y, array $x, float $slope, float $intercept): float
    {
        $yMean = array_sum($y) / count($y);
        $ssTot = 0;
        $ssRes = 0;

        for ($i = 0; $i < count($y); $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $ssTot += ($y[$i] - $yMean) ** 2;
            $ssRes += ($y[$i] - $predicted) ** 2;
        }

        return $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;
    }

    private function calculateStdDev(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($v) use ($mean) {
            return ($v - $mean) ** 2;
        }, $values)) / count($values);
        
        return sqrt($variance);
    }
}
