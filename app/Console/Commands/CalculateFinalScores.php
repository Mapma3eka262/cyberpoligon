<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeamTask;
use Carbon\Carbon;

class CalculateFinalScores extends Command
{
    protected $signature = 'scores:calculate-final';
    protected $description = 'Calculate final scores for completed tasks (apply penalties)';

    public function handle()
    {
        $this->info('Calculating final scores...');
        
        // Находим все завершенные задания (время вышло ИЛИ оба флага найдены)
        $teamTasks = TeamTask::where(function($query) {
            $query->where('end_time', '<', Carbon::now())
                  ->orWhere(function($q) {
                      $q->where('flag1_found', true)
                        ->where('flag2_found', true);
                  });
        })->whereNull('final_score') // Только те, где финальный счет еще не рассчитан
          ->get();

        foreach ($teamTasks as $teamTask) {
            $baseScore = 0;
            
            if ($teamTask->flag1_found) $baseScore += 30;
            if ($teamTask->flag2_found) $baseScore += 70;
            
            $penalty = $teamTask->wrong_attempts_count * 3;
            $finalScore = max(0, $baseScore - $penalty);
            
            $teamTask->update([
                'score' => $finalScore,
                'final_score' => $finalScore,
            ]);
            
            $this->info("Team {$teamTask->team_id}, Task {$teamTask->task_id}: {$baseScore} - {$penalty} = {$finalScore}");
        }
        
        $this->info('Done! Calculated for ' . $teamTasks->count() . ' tasks.');
        return 0;
    }
}
