<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Показать статистику
     */
    public function index()
    {
        // Текущие активные задания
        $activeTasks = Task::where('is_active', true)->get();
        
        // Статистика по командам для каждого задания
        $teamStats = [];
        
        foreach ($activeTasks as $task) {
            $teamStats[$task->id] = [
                'task' => $task,
                'teams' => DB::table('team_task')
                    ->join('teams', 'team_task.team_id', '=', 'teams.id')
                    ->where('task_id', $task->id)
                    ->select([
                        'teams.id',
                        'teams.name as team_name',
                        'team_task.flag1_found',
                        'team_task.flag2_found',
                        'team_task.wrong_attempts_count',
                        'team_task.score',
                        'team_task.start_time',
                        'team_task.end_time',
                    ])
                    ->orderBy('team_task.score', 'desc')
                    ->get()
                    ->map(function($team) use ($task) {
                        // Рассчитываем прогресс
                        $timeElapsed = now()->diffInMinutes($team->start_time);
                        $timeTotal = $task->time_limit_minutes;
                        $progressPercent = min(100, ($timeElapsed / $timeTotal) * 100);
                        
                        // Рассчитываем оставшееся время
                        $remainingMinutes = max(0, $task->time_limit_minutes - $timeElapsed);
                        $remainingTime = sprintf('%02d:%02d', 
                            floor($remainingMinutes / 60), 
                            $remainingMinutes % 60
                        );
                        
                        return [
                            'id' => $team->id,
                            'name' => $team->team_name,
                            'flag1_found' => (bool) $team->flag1_found,
                            'flag2_found' => (bool) $team->flag2_found,
                            'wrong_attempts' => $team->wrong_attempts_count,
                            'score' => $team->score,
                            'progress_percent' => $progressPercent,
                            'remaining_time' => $remainingTime,
                            'status' => $this->getTeamStatus($team, $task),
                        ];
                    }),
            ];
        }

        // Общая статистика
        $totalStats = [
            'total_teams' => Team::count(),
            'active_teams' => DB::table('team_task')
                ->where('end_time', '>', now())
                ->distinct('team_id')
                ->count('team_id'),
            'total_tasks' => Task::count(),
            'active_tasks' => Task::where('is_active', true)->count(),
            'total_flag_attempts' => DB::table('flag_attempts')->count(),
            'correct_attempts' => DB::table('flag_attempts')->where('is_correct', true)->count(),
            'success_rate' => DB::table('flag_attempts')->count() > 0 ?
                round((DB::table('flag_attempts')->where('is_correct', true)->count() / DB::table('flag_attempts')->count()) * 100, 2) : 0,
        ];

        return view('admin.stats', compact('teamStats', 'totalStats', 'activeTasks'));
    }

    /**
     * Определить статус команды
     */
    private function getTeamStatus($team, $task)
    {
        if ($team->flag1_found && $team->flag2_found) {
            return 'completed';
        }
        
        $timeElapsed = now()->diffInMinutes($team->start_time);
        
        if ($timeElapsed >= $task->time_limit_minutes) {
            return 'timeout';
        }
        
        if ($team->flag1_found || $team->flag2_found) {
            return 'in_progress';
        }
        
        return 'not_started';
    }

    /**
     * Экспорт статистики в CSV
     */
    public function exportCsv()
    {
        $teams = Team::with(['tasks' => function($query) {
            $query->wherePivot('end_time', '>', now()->subDays(1));
        }])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ctf_stats_' . date('Y-m-d_H-i') . '.csv"',
        ];

        $callback = function() use ($teams) {
            $file = fopen('php://output', 'w');
            
            // Заголовки
            fputcsv($file, [
                'Команда',
                'Капитан',
                'Участников',
                'Активных заданий',
                'Найдено флагов',
                'Неправильных попыток',
                'Общий счёт',
                'Последняя активность',
            ]);

            // Данные
            foreach ($teams as $team) {
                $foundFlags = 0;
                $wrongAttempts = 0;
                $totalScore = 0;
                
                foreach ($team->tasks as $task) {
                    $foundFlags += ($task->pivot->flag1_found ? 1 : 0);
                    $foundFlags += ($task->pivot->flag2_found ? 1 : 0);
                    $wrongAttempts += $task->pivot->wrong_attempts_count;
                    $totalScore += $task->pivot->score;
                }
                
                fputcsv($file, [
                    $team->name,
                    $team->captain ? $team->captain->name . ' ' . $team->captain->surname : 'Нет',
                    $team->members()->count(),
                    $team->tasks()->wherePivot('end_time', '>', now())->count(),
                    $foundFlags,
                    $wrongAttempts,
                    $totalScore,
                    $team->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}