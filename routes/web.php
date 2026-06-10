<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArenaController;
use App\Http\Controllers\OsintArenaController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ProxmoxController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/privacy', function () {
    return response()->file(public_path('privacy.pdf'));
})->name('privacy');

// Аутентификация
Route::get('/login-register', [AuthController::class, 'showLoginRegister'])->name('login.register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Тестовые маршруты
Route::get('/test', [TestController::class, 'index']);
Route::get('/health', [TestController::class, 'health']);

// Защищенные маршруты
Route::middleware(['auth'])->group(function () {
    // Личный кабинет
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update-contact', [DashboardController::class, 'updateContactInfo'])->name('dashboard.update-contact');
    Route::post('/dashboard/change-password', [DashboardController::class, 'changePassword'])->name('dashboard.change-password');
    
    // Арена CTF
    Route::get('/arena/ctf', [ArenaController::class, 'index'])->name('arena.ctf');
    Route::post('/arena/submit-flag', [ArenaController::class, 'submitFlag'])->name('arena.submit-flag');
    
    // Арена OSINT
    Route::get('/arena/osint', [OsintArenaController::class, 'index'])->name('arena.osint');
    Route::get('/arena/osint/{userTask}', [OsintArenaController::class, 'show'])->name('arena.osint.show');
    Route::post('/arena/osint/{userTask}/answer', [OsintArenaController::class, 'submitAnswer'])->name('arena.osint.answer');
    Route::post('/arena/osint/{userTask}/hint', [OsintArenaController::class, 'requestHint'])->name('arena.osint.hint');
    
    // Guide
    Route::get('/guide', function () {
        return response()->file(public_path('guide.pdf'));
    })->name('guide');
    
    // Регистрация участников капитаном
    Route::post('/register-participant', [AuthController::class, 'registerParticipant'])->name('register.participant');

    // Proxmox VM доступ
    Route::get('/tasks/{task}/vm-console', [ProxmoxController::class, 'getVmConsole'])->name('tasks.vm-console');
    Route::get('/tasks/{task}/vm-status', [ProxmoxController::class, 'getVmStatus'])->name('tasks.vm-status');
});

// Админ маршруты
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    require __DIR__.'/admin.php';
});

// Регистрация участников капитаном (дубль, но оставлю для совместимости)
Route::post('/register-participant', [AuthController::class, 'registerParticipant'])->name('register.participant');
Route::post('/add-existing-participant', [AuthController::class, 'addExistingParticipant'])->name('add.existing.participant');