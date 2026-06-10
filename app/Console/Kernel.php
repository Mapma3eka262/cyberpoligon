<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CalculateFinalScores::class,
        // другие команды, если есть
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Собирать метрики каждые 5 минут
        $schedule->command('metrics:collect')->everyFiveMinutes();

	$schedule->command('forecasts:generate --hours=1')->hourly();
	$schedule->command('forecasts:evaluate --hours=24')->daily();
        
        // Очищать старые сессии каждый день
        $schedule->command('session:gc')->daily();

        // Каждую минуту проверяем и рассчитываем финальные счета
        $schedule->command('scores:calculate-final')->everyMinute();
        
        // Завершать просроченные задания
        $schedule->call(function () {
            \DB::table('team_task')
                ->where('end_time', '<', now())
                ->where('flag1_found', false)
                ->where('flag2_found', false)
                ->update([
                    'flag1_found' => false,
                    'flag2_found' => false,
                ]);
        })->hourly();
    }
    
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
