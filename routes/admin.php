<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\ForecastController;

// Аналитика
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
Route::get('/analytics/metrics', [AnalyticsController::class, 'getMetricsJson'])->name('analytics.metrics');

// Управление командами
Route::get('/teams', [TeamController::class, 'index'])->name('teams');
Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
Route::post('/teams/create-user', [TeamController::class, 'createUser'])->name('teams.create-user');
Route::delete('/teams/users/{user}', [TeamController::class, 'destroyUser'])->name('teams.destroy-user');

// Управление заданиями
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

// Статистика
Route::get('/stats', [StatsController::class, 'index'])->name('stats');
Route::get('/stats/export', [StatsController::class, 'exportCsv'])->name('stats.export');

// Прогнозы
Route::prefix('forecasts')->group(function () {
    Route::get('/', [ForecastController::class, 'index'])->name('forecasts.index');
    Route::get('/accuracy', [ForecastController::class, 'accuracy'])->name('forecasts.accuracy');
    Route::post('/generate', [ForecastController::class, 'generateNow'])->name('forecasts.generate');
    Route::post('/evaluate', [ForecastController::class, 'evaluateNow'])->name('forecasts.evaluate');
    Route::get('/chart-data', [ForecastController::class, 'chartData'])->name('forecasts.chart-data');
});