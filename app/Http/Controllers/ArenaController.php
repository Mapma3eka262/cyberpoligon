<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Team;
use App\Models\FlagAttempt;
use App\Models\TeamTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ArenaController extends Controller
{
    /**
     * Показать страницу арены
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->team;
        
        if (!$team) {
            return redirect()->route('dashboard')->with('error', 'Вы не состоите в команде.');
        }

        $activeTask = $team->activeTask();
        
        if (!$activeTask) {
            return view('arena', [
                'team' => $team,
                'activeTask' => null,
                'flagAttempts' => collect(),
                'timeRemaining' => null,
                'currentScore' => 0,
            ]);
        }

        $teamTask = TeamTask::where('team_id', $team->id)
            ->where('task_id', $activeTask->id)
            ->first();

        $flagAttempts = FlagAttempt::where('team_task_id', $teamTask->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $activeTask->pivot = $teamTask;

        // Рассчитываем текущий счет (без вычета штрафов)
        $currentScore = $this->calculateCurrentScore($teamTask);
        
        return view('arena', [
            'team' => $team,
            'activeTask' => $activeTask,
            'flagAttempts' => $flagAttempts,
            'timeRemaining' => $this->calculateTimeRemaining($teamTask->end_time),
            'currentScore' => $currentScore,
        ]);
    }

    /**
     * Отправить флаг на проверку
     */
    public function submitFlag(Request $request)
    {
        $validated = $request->validate([
            'flag_number' => 'required|in:1,2',
            'flag_value' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $team = $user->team;
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не состоите в команде.',
            ], 403);
        }

        $activeTask = $team->activeTask();
        
        if (!$activeTask) {
            return response()->json([
                'success' => false,
                'message' => 'Нет активного задания.',
            ], 400);
        }

        $teamTask = TeamTask::where('team_id', $team->id)
            ->where('task_id', $activeTask->id)
            ->first();
        
        if (!$teamTask) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка данных задания.',
            ], 500);
        }
        
        // Проверяем, не истекло ли время
        if ($teamTask->end_time && Carbon::now()->greaterThan($teamTask->end_time)) {
            return response()->json([
                'success' => false,
                'message' => 'Время выполнения задания истекло. Флаги не принимаются.',
            ], 400);
        }
        
        // Проверяем, найден ли уже этот флаг
        if (($validated['flag_number'] == 1 && $teamTask->flag1_found) ||
            ($validated['flag_number'] == 2 && $teamTask->flag2_found)) {
            return response()->json([
                'success' => false,
                'message' => 'Этот флаг уже найден.',
            ], 400);
        }

        $flagHash = $validated['flag_number'] == 1 ? $activeTask->flag1_hash : $activeTask->flag2_hash;
        $isCorrect = Hash::check($validated['flag_value'], $flagHash);

        // Записываем попытку
        FlagAttempt::create([
            'user_id' => $user->id,
            'team_task_id' => $teamTask->id,
            'flag_number' => $validated['flag_number'],
            'attempt_value' => $validated['flag_value'],
            'is_correct' => $isCorrect,
        ]);

        if ($isCorrect) {
            // Начисляем баллы за флаг (30 или 70)
            $flagPoints = $validated['flag_number'] == 1 ? 30 : 70;
            
            // Обновляем статус флага
            $updateData = [];
            
            if ($validated['flag_number'] == 1) {
                $updateData['flag1_found'] = true;
            } else {
                $updateData['flag2_found'] = true;
            }
            
            $teamTask->update($updateData);
            
            // Рассчитываем текущий счет (без штрафов)
            $currentScore = $this->calculateCurrentScore($teamTask);
            
            return response()->json([
                'success' => true,
                'message' => 'Флаг принят! +' . $flagPoints . ' баллов',
                'flag_number' => $validated['flag_number'],
                'score' => $currentScore, // Временный счет без штрафов
            ]);
        } else {
            // Увеличиваем счетчик неправильных попыток
            $teamTask->increment('wrong_attempts_count');
            
            // Рассчитываем текущий счет (без штрафов)
            $currentScore = $this->calculateCurrentScore($teamTask);
            
            return response()->json([
                'success' => false,
                'message' => 'Неверный флаг. После окончания задания: -3 балла за каждую ошибку',
                'wrong_attempts' => $teamTask->wrong_attempts_count,
                'score' => $currentScore, // Временный счет без штрафов
            ]);
        }
    }

    /**
     * Рассчитать оставшееся время задания
     */
    private function calculateTimeRemaining($endTime)
    {
        if (!$endTime) {
            return [
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'is_expired' => true,
            ];
        }
        
        $end = Carbon::parse($endTime);
        $now = Carbon::now();
        
        if ($now >= $end) {
            return [
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'is_expired' => true,
            ];
        }
        
        $diff = $end->diffInSeconds($now);
        
        return [
            'hours' => floor($diff / 3600),
            'minutes' => floor(($diff % 3600) / 60),
            'seconds' => $diff % 60,
            'is_expired' => false,
        ];
    }
    
    /**
     * Рассчитать текущий счет (без вычета штрафов)
     */
    private function calculateCurrentScore($teamTask)
    {
        $score = 0;
        
        if ($teamTask->flag1_found) {
            $score += 30;
        }
        
        if ($teamTask->flag2_found) {
            $score += 70;
        }
        
        return $score;
    }
    
    /**
     * Рассчитать финальный счет (с вычетом штрафов)
     * Вызывается при завершении задания
     */
    private function calculateFinalScore($teamTask)
    {
        $score = $this->calculateCurrentScore($teamTask);
        
        // Вычитаем штрафы за неправильные попытки
        $penalty = $teamTask->wrong_attempts_count * 3;
        $finalScore = max(0, $score - $penalty);
        
        return $finalScore;
    }
}
