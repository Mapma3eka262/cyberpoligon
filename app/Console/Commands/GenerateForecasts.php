<?php

namespace App\Console\Commands;

use App\Services\Math\ForecastService;
use Illuminate\Console\Command;

class GenerateForecasts extends Command
{
    protected $signature = 'forecasts:generate {--hours=1 : На сколько часов вперед прогноз}';
    protected $description = 'Генерация прогнозных метрик';

    public function handle(ForecastService $forecastService): void
    {
        $hours = (int) $this->option('hours');
        $minutes = $hours * 60;
        
        $this->info("Генерация прогнозов на {$hours} час(ов) вперед...");
        
        $forecastService->generateAllForecasts($minutes);
        
        $this->info('Прогнозы успешно сгенерированы!');
    }
}
