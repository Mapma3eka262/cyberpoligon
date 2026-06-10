<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('teams', 'userTasks.user')->paginate(20);
        $teams = Team::all();
        $users = User::where('role', 'participant')->get();
        
        return view('admin.tasks', compact('tasks', 'teams', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:ctf,osint',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            
            // CTF поля
            'target_ip' => 'nullable|string|max:50',
            'subnet_mask' => 'nullable|string|max:50',
            'flag1_value' => 'nullable|string|max:255',
            'flag2_value' => 'nullable|string|max:255',
            'vm_id' => 'nullable|integer|min:1',
            'proxmox_node' => 'nullable|string|max:50',
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id',
            
            // OSINT поля
            'osint_expected_answers' => 'nullable|array',
            'osint_points_per_answer' => 'nullable|array',
            'osint_hints' => 'nullable|array',
            'osint_max_score' => 'nullable|integer|min:1',
            'osint_sources' => 'nullable|array',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $taskData = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'ctf') {
            $taskData['target_ip'] = $validated['target_ip'] ?? '';
            $taskData['subnet_mask'] = $validated['subnet_mask'] ?? null;
            $taskData['flag1_hash'] = Hash::make($validated['flag1_value'] ?? '');
            $taskData['flag2_hash'] = Hash::make($validated['flag2_value'] ?? '');
            $taskData['vm_id'] = $validated['vm_id'] ?? null;
            $taskData['proxmox_node'] = $validated['proxmox_node'] ?? 'pve';
        } else {
            $taskData['osint_data'] = [
                'expected_answers' => $validated['osint_expected_answers'] ?? [],
                'points_per_answer' => $validated['osint_points_per_answer'] ?? [],
                'hints' => $validated['osint_hints'] ?? [],
                'max_score' => $validated['osint_max_score'] ?? 100,
                'sources' => $validated['osint_sources'] ?? [],
            ];
        }

        $task = Task::create($taskData);

        // Назначение для CTF
        if ($validated['type'] === 'ctf' && !empty($validated['teams'])) {
            foreach ($validated['teams'] as $teamId) {
                $startTime = now();
                $endTime = $validated['time_limit_minutes'] 
                    ? now()->addMinutes($validated['time_limit_minutes']) 
                    : now()->addHours(24);
                
                $task->teams()->attach($teamId, [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'flag1_found' => false,
                    'flag2_found' => false,
                    'wrong_attempts_count' => 0,
                    'score' => 0,
                ]);
            }
        }

        // Назначение для OSINT
        if ($validated['type'] === 'osint' && !empty($validated['users'])) {
            foreach ($validated['users'] as $userId) {
                UserTask::create([
                    'user_id' => $userId,
                    'task_id' => $task->id,
                    'start_time' => now(),
                    'end_time' => $validated['time_limit_minutes'] 
                        ? now()->addMinutes($validated['time_limit_minutes']) 
                        : null,
                    'score' => 0,
                    'completed' => false,
                ]);
            }
        }

        return back()->with('success', 'Задание успешно создано.');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'target_ip' => 'nullable|string|max:50',
            'subnet_mask' => 'nullable|string|max:50',
            'vm_id' => 'nullable|integer|min:1',
            'proxmox_node' => 'nullable|string|max:50',
        ]);

        $task->update($validated);

        return back()->with('success', 'Задание успешно обновлено.');
    }

    public function destroy(Task $task)
    {
        $task->teams()->detach();
        $task->userTasks()->delete();
        $task->delete();

        return back()->with('success', 'Задание успешно удалено.');
    }
}