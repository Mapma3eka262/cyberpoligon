<?php

namespace App\Http\Controllers;

use App\Models\UserTask;
use App\Models\Task;
use App\Models\OsintAnswer;
use App\Models\OsintHintUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OsintArenaController extends Controller
{
    /**
     * Список OSINT-заданий пользователя
     */
    public function index()
    {
        $user = Auth::user();
        
        $userTasks = UserTask::with('task')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $activeUserTask = $userTasks->where('completed', false)
            ->filter(function ($ut) {
                return !$ut->end_time || now()->lt($ut->end_time);
            })
            ->first();
        
        return view('osint-arena.index', [
            'userTasks' => $userTasks,
            'activeUserTask' => $activeUserTask,
            'activeTask' => $activeUserTask ? $activeUserTask->task : null,
        ]);
    }

    /**
     * Показать конкретное OSINT-задание
     */
    public function show(UserTask $userTask)
    {
        $user = Auth::user();
        
        if ($userTask->user_id !== $user->id) {
            abort(403);
        }
        
        $task = $userTask->task;
        $osintData = json_decode($task->osint_data, true);
        
        if (!$osintData) {
            $osintData = [
                'expected_answers' => [],
                'points_per_answer' => [],
                'hints' => [],
                'max_score' => 100,
                'sources' => [],
            ];
        }
        
        // Получаем ответы пользователя
        $answers = OsintAnswer::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('question_key');
        
        // Полученные подсказки
        $usedHints = OsintHintUsage::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->pluck('hint_index')
            ->toArray();
        
        return view('osint-arena.task', [
            'userTask' => $userTask,
            'task' => $task,
            'osintData' => $osintData,
            'answers' => $answers,
            'usedHints' => $usedHints,
            'timeRemaining' => $userTask->getRemainingTime(),
        ]);
    }

    /**
     * Отправить ответ на вопрос
     */
    public function submitAnswer(Request $request, UserTask $userTask)
    {
        $user = Auth::user();
        
        if ($userTask->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещён'], 403);
        }
        
        // Проверяем, активно ли задание
        if (!$userTask->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Задание уже завершено или время истекло'
            ], 403);
        }
        
        $validated = $request->validate([
            'key' => 'required|string',
            'answer' => 'required|string|max:500',
        ]);
        
        $task = $userTask->task;
        $osintData = json_decode($task->osint_data, true);
        
        $expectedAnswers = $osintData['expected_answers'] ?? [];
        $pointsPerAnswer = $osintData['points_per_answer'] ?? [];
        
        // Ищем ожидаемый ответ по ключу
        $expected = null;
        $points = 0;
        foreach ($expectedAnswers as $index => $item) {
            if ($item['key'] === $validated['key']) {
                $expected = trim($item['answer']);
                $points = $pointsPerAnswer[$index] ?? 10;
                break;
            }
        }
        
        if ($expected === null) {
            return response()->json([
                'success' => false,
                'message' => 'Неизвестный вопрос'
            ], 400);
        }
        
        // Проверяем, не отвечал ли уже правильно
        $existingCorrect = OsintAnswer::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->where('question_key', $validated['key'])
            ->where('is_correct', true)
            ->exists();
        
        if ($existingCorrect) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже правильно ответили на этот вопрос'
            ]);
        }
        
        $isCorrect = (strcasecmp(trim($validated['answer']), $expected) === 0);
        
        $answerRecord = OsintAnswer::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'question_key' => $validated['key'],
            'user_answer' => $validated['answer'],
            'is_correct' => $isCorrect,
            'points_awarded' => $isCorrect ? $points : 0,
        ]);
        
        if ($isCorrect) {
            // Начисляем баллы
            $userTask->increment('score', $points);
            
            // Проверяем, все ли вопросы решены
            $totalQuestions = count($expectedAnswers);
            $correctAnswers = OsintAnswer::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->where('is_correct', true)
                ->count();
            
            $completed = ($correctAnswers >= $totalQuestions);
            if ($completed) {
                $userTask->update(['completed' => true]);
            }
            
            return response()->json([
                'success' => true,
                'message' => "✅ Верно! +{$points} баллов",
                'points' => $points,
                'total_score' => $userTask->score,
                'completed' => $completed,
                'question_key' => $validated['key'],
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => '❌ Неверный ответ. Попробуйте ещё раз.',
            'total_score' => $userTask->score,
        ]);
    }

    /**
     * Запросить подсказку (со штрафом)
     */
    public function requestHint(Request $request, UserTask $userTask)
    {
        $user = Auth::user();
        
        if ($userTask->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещён'], 403);
        }
        
        if (!$userTask->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Задание уже завершено'
            ], 403);
        }
        
        $validated = $request->validate([
            'hint_index' => 'required|integer|min:0',
        ]);
        
        $task = $userTask->task;
        $osintData = json_decode($task->osint_data, true);
        $hints = $osintData['hints'] ?? [];
        
        if (!isset($hints[$validated['hint_index']])) {
            return response()->json([
                'success' => false,
                'message' => 'Подсказка не найдена'
            ], 404);
        }
        
        $hint = $hints[$validated['hint_index']];
        $penalty = $hint['cost'] ?? 5;
        
        // Проверяем, не использовал ли уже эту подсказку
        $alreadyUsed = OsintHintUsage::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->where('hint_index', $validated['hint_index'])
            ->exists();
        
        if ($alreadyUsed) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже использовали эту подсказку'
            ]);
        }
        
        // Списываем штраф (баллы не могут быть отрицательными)
        $newScore = max(0, $userTask->score - $penalty);
        $userTask->update(['score' => $newScore]);
        
        OsintHintUsage::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'hint_index' => $validated['hint_index'],
            'penalty_points' => $penalty,
        ]);
        
        return response()->json([
            'success' => true,
            'hint_text' => $hint['text'],
            'penalty' => $penalty,
            'new_score' => $newScore,
        ]);
    }
}