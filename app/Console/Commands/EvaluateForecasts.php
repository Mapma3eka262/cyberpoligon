<?php

namespace App\Console\Commands;

use App\Services\Math\AccuracyService;
use Illuminate\Console\Command;

class EvaluateForecasts extends Command
{
    protected $signature = 'forecasts:evaluate {--hours=24 : За сколько часов оценить прогнозы}';
    protected $description = 'Оценка точности прогнозов';

    public function handle(AccuracyService $accuracyService): void
    {
        $hours = (int) $this->option('hours');
        
        $this->info("Оценка точности прогнозов за последние {$hours} часов...");
        
        $results = $accuracyService->evaluateForecasts($hours);
        
        $this->table(
            ['Метрика', 'Метод', 'Прогноз', 'Факт', 'MAE', 'MAPE(%)', 'RMSE'],
            collect($results)->map(function($item) {
                return [
                    $item['metric'],
                    $item['method'],
                    round($item['forecast'], 2),
                    round($item['actual'], 2),
                    round($item['mae'], 2),
                    round($item['mape'], 2),
                    round($item['rmse'], 2),
                ];
            })
        );
        
        // показать лучший метод для каждой метрики
        $bestMethods = $accuracyService->getBestMethodPerMetric();
        $this->newLine();
        $this->info('Лучшие методы прогнозирования (по MAPE за 7 дней):');
        
        foreach ($bestMethods as $metric => $data) {
            $this->line("  - {$metric}: {$data['method']} (MAPE: {$data['avg_mape']}%)");
        }
        
        $this->info('Оценка завершена!');
    }
}
