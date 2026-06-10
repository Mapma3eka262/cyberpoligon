<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forecast;
use App\Models\SystemMetric;
use App\Services\Math\AccuracyService;
use App\Services\Math\ForecastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForecastController extends Controller
{
    protected $forecastService;
    protected $accuracyService;

    public function __construct(ForecastService $forecastService, AccuracyService $accuracyService)
    {
        $this->forecastService = $forecastService;
        $this->accuracyService = $accuracyService;
    }

    public function index(Request $request)
    {
        $metricType = $request->get('metric', 'cpu_usage');
        $method = $request->get('method');
        
        $forecasts = Forecast::where('metric_type', $metricType)
            ->orderBy('forecasted_at', 'desc')
            ->paginate(20);
        
        $latestActual = SystemMetric::latest('recorded_at')->first();
        
        $bestMethods = $this->accuracyService->getBestMethodPerMetric();
        
        return view('admin.forecasts.index', compact('forecasts', 'metricType', 'latestActual', 'bestMethods'));
    }

    public function accuracy()
    {
        $metrics = DB::table('forecast_accuracy')
            ->join('forecasts', 'forecast_accuracy.forecast_id', '=', 'forecasts.id')
            ->select(
                'forecasts.metric_type',
                'forecasts.forecast_method',
                DB::raw('AVG(forecast_accuracy.absolute_error) as avg_mae'),
                DB::raw('AVG(forecast_accuracy.percentage_error) as avg_mape'),
                DB::raw('SQRT(AVG(forecast_accuracy.squared_error)) as avg_rmse'),
                DB::raw('COUNT(*) as predictions_count')
            )
            ->where('forecast_accuracy.created_at', '>=', now()->subDays(7))
            ->groupBy('forecasts.metric_type', 'forecasts.forecast_method')
            ->orderBy('forecasts.metric_type')
            ->orderBy('avg_mape')
            ->get();
        
        $r2Scores = [];
        foreach (['cpu_usage', 'memory_usage', 'disk_usage', 'active_connections'] as $metric) {
            $r2Scores[$metric] = $this->accuracyService->calculateR2Score($metric);
        }
        
        return view('admin.forecasts.accuracy', compact('metrics', 'r2Scores'));
    }

    public function generateNow(Request $request)
    {
        $horizon = $request->get('horizon', 60);
        $this->forecastService->generateAllForecasts($horizon);
        
        return redirect()->route('admin.forecasts.index')
            ->with('success', "Прогнозы на {$horizon} минут сгенерированы");
    }

    public function evaluateNow()
    {
        $results = $this->accuracyService->evaluateForecasts(24);
        
        return redirect()->route('admin.forecasts.accuracy')
            ->with('success', "Оценено " . count($results) . " прогнозов");
    }

    public function chartData(Request $request)
    {
        $metricType = $request->get('metric', 'cpu_usage');
        $hours = $request->get('hours', 24);
        
        $historical = SystemMetric::where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get(['recorded_at', $metricType]);
        
        $forecasts = Forecast::where('metric_type', $metricType)
            ->where('forecasted_at', '>=', now())
            ->where('forecasted_at', '<=', now()->addHours($hours))
            ->orderBy('forecasted_at')
            ->get();
        
        return response()->json([
            'historical' => $historical->map(fn($m) => [
                'timestamp' => $m->recorded_at->timestamp * 1000,
                'value' => (float) $m->{$metricType},
            ]),
            'forecasts' => $forecasts->map(fn($f) => [
                'timestamp' => $f->forecasted_at->timestamp * 1000,
                'value' => (float) $f->forecast_value,
                'method' => $f->forecast_method,
                'lower' => (float) ($f->lower_bound ?? $f->forecast_value * 0.9),
                'upper' => (float) ($f->upper_bound ?? $f->forecast_value * 1.1),
            ]),
        ]);
    }
}
