<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Публичные API маршруты
Route::middleware('api')->group(function () {
    // Получение метрик для аналитики
    Route::get('/metrics', [AnalyticsController::class, 'getMetricsJson']);
    
    // Получение логов системы
    Route::get('/logs', function() {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response()->json([]);
        }
        
        $logs = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $recentLines = array_slice($lines, -20); // Последние 20 строк
        
        foreach ($recentLines as $line) {
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(\w+)\./', $line, $matches)) {
                $time = $matches[1];
                $type = $matches[2];
                
                // Извлекаем сообщение
                $message = substr($line, strpos($line, '] ') + 2);
                $message = preg_replace('/\w+\.\w+: /', '', $message);
                
                $logs[] = [
                    'time' => $time,
                    'type' => $type,
                    'message' => substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''),
                ];
            }
        }
        
        return response()->json(array_reverse($logs));
    });
    
    // Получение пользователей без команды (для выбора капитана)
    Route::get('/users/without-team', function() {
        $users = \App\Models\User::whereNull('team_id')
            ->where('role', '!=', 'admin')
            ->select('id', 'surname', 'name', 'email')
            ->get();
        
        return response()->json($users);
    });
});