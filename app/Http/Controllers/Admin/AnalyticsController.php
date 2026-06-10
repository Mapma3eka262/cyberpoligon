<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Показать аналитику системы
     */
    public function index()
    {
        // Получаем последние метрики
        $latestMetrics = SystemMetric::latest()->first();
        
        // Если нет метрик или они устарели (больше 5 минут), собираем новые
        if (!$latestMetrics || $latestMetrics->created_at->diffInMinutes(now()) > 5) {
            $latestMetrics = $this->collectSystemMetrics();
        }

        // Получаем историю метрик за последние 24 часа
        $metricsHistory = SystemMetric::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at')
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('H:00');
            })
            ->map(function($group) {
                return [
                    'cpu_usage' => round($group->avg('cpu_usage'), 2),
                    'memory_usage' => round($group->avg('memory_usage'), 2),
                    'disk_usage' => round($group->avg('disk_usage'), 2),
                    'active_connections' => round($group->avg('active_connections')),
                ];
            });

        // Статистика активности
        $activeUsers = Cache::remember('active_users_count', 60, function() {
            return DB::table('sessions')
                ->where('last_activity', '>', now()->subMinutes(15)->timestamp)
                ->count();
        });

        $activeTeams = Cache::remember('active_teams_count', 60, function() {
            return DB::table('team_task')
                ->where('end_time', '>', now())
                ->distinct('team_id')
                ->count('team_id');
        });

        return view('admin.analytics', compact(
            'latestMetrics',
            'metricsHistory',
            'activeUsers',
            'activeTeams'
        ));
    }

    /**
     * Собрать метрики системы
     */
    private function collectSystemMetrics()
    {
        // CPU usage - ИСПРАВЛЕНО: конвертируем load average в проценты
        $cpuUsage = 0;
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            // Получаем количество ядер CPU
            $cores = 1;
            if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/cpuinfo')) {
                $cores = (int) shell_exec('nproc 2>/dev/null') ?: 4;
            }
            // Конвертируем load average в проценты: (load / cores) * 100
            $cpuUsage = round(($load[0] / $cores) * 100, 2);
            // Ограничиваем 100%
            $cpuUsage = min($cpuUsage, 100);
        }
        
        // Memory usage
        $memoryUsage = 0;
        if (PHP_OS_FAMILY === 'Linux' && function_exists('shell_exec')) {
            $memoryInfo = shell_exec('free 2>/dev/null');
            if ($memoryInfo) {
                $memoryLines = explode("\n", $memoryInfo);
                if (isset($memoryLines[1])) {
                    $memoryValues = preg_split('/\s+/', trim($memoryLines[1]));
                    if (count($memoryValues) >= 3) {
                        $totalMemory = (int)$memoryValues[1];
                        $usedMemory = (int)$memoryValues[2];
                        $memoryUsage = $totalMemory > 0 ? round(($usedMemory / $totalMemory) * 100, 2) : 0;
                    }
                }
            }
        }
        
        // Disk usage
        $diskUsage = 0;
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        if ($diskFree && $diskTotal) {
            $diskUsage = round((1 - ($diskFree / $diskTotal)) * 100, 2);
        }
        
        // Active connections
        try {
            $activeConnections = DB::select('SHOW PROCESSLIST');
            $connectionCount = count($activeConnections);
        } catch (\Exception $e) {
            $connectionCount = 0;
        }

        // Сохраняем метрики
        $metrics = SystemMetric::create([
            'cpu_usage' => $cpuUsage,
            'memory_usage' => $memoryUsage,
            'disk_usage' => $diskUsage,
            'active_connections' => $connectionCount,
            'recorded_at' => now(),
        ]);

        // Очищаем старые записи (оставляем только за 7 дней)
        SystemMetric::where('created_at', '<', now()->subDays(7))->delete();

        return $metrics;
    }

    /**
     * Получить метрики в формате JSON (для AJAX)
     */
    public function getMetricsJson()
    {
        $metrics = $this->collectSystemMetrics();
        
        return response()->json([
            'cpu_usage' => round($metrics->cpu_usage, 1),
            'memory_usage' => round($metrics->memory_usage, 1),
            'disk_usage' => round($metrics->disk_usage, 1),
            'active_connections' => $metrics->active_connections,
            'updated_at' => $metrics->created_at->format('H:i:s'),
        ]);
    }
}
