<?php

namespace App\Console\Commands;

use App\Models\SystemMetric;
use App\Services\Math\ForecastService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectMetrics extends Command
{
    protected $signature = 'metrics:collect';
    protected $description = 'Собрать метрики системы';

    public function handle(): void
    {
        $cpuUsage = sys_getloadavg()[0] * 100;
        
        $memoryInfo = shell_exec('free -m');
        $memoryLines = explode("\n", $memoryInfo);
        $memoryValues = preg_split('/\s+/', $memoryLines[1]);
        
        $totalMemory = $memoryValues[1];
        $usedMemory = $memoryValues[2];
        $memoryUsage = ($usedMemory / $totalMemory) * 100;
        
        $diskUsage = disk_free_space('/') ? 
            (1 - (disk_free_space('/') / disk_total_space('/'))) * 100 : 0;
        
        $activeConnections = DB::select('SHOW PROCESSLIST');
        $connectionCount = count($activeConnections);

        SystemMetric::create([
            'cpu_usage' => $cpuUsage,
            'memory_usage' => $memoryUsage,
            'disk_usage' => $diskUsage,
            'active_connections' => $connectionCount,
            'recorded_at' => now(),
        ]);

        // Очищаем старые записи
        SystemMetric::where('created_at', '<', now()->subDays(7))->delete();

	$lastMetricCount = SystemMetric::count();
        if ($lastMetricCount % 4 == 0) {
            $forecastService = app(ForecastService::class);
            $forecastService->generateAllForecasts(60); // прогноз на 1 час
            $this->info('Прогнозы сгенерированы');
        }

        $this->info('Метрики собраны: CPU=' . round($cpuUsage, 2) . '%, RAM=' . round($memoryUsage, 2) . '%, Disk=' . round($diskUsage, 2) . '%');
    }
}
