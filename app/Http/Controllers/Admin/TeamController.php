<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Показать список команд
     */
    public function index()
    {
        $teams = Team::with(['captain', 'members'])->paginate(20);
        $users = User::with('team')->paginate(20); // Убрал неверный третий параметр
        
        return view('admin.teams', compact('teams', 'users'));
    }

    /**
     * Создать новую команду
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:teams|max:100',
            'captain_id' => 'required|exists:users,id',
        ]);

        $team = Team::create($validated);

        // Обновляем команду у капитана
        User::where('id', $validated['captain_id'])->update([
            'team_id' => $team->id,
            'role' => 'captain',
        ]);

        return back()->with('success', 'Команда успешно создана.');
    }

    /**
     * Обновить команду
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:teams,name,' . $team->id,
            'captain_id' => 'required|exists:users,id',
        ]);

        // Если сменился капитан
        if ($team->captain_id != $validated['captain_id']) {
            // Старого капитана делаем участником
            User::where('id', $team->captain_id)->update(['role' => 'participant']);
            
            // Нового капитана делаем капитаном
            User::where('id', $validated['captain_id'])->update([
                'role' => 'captain',
                'team_id' => $team->id,
            ]);
        }

        $team->update($validated);

        return back()->with('success', 'Команда успешно обновлена.');
    }

    /**
     * Удалить команду
     */
    public function destroy(Team $team)
    {
        // Сбрасываем команду у всех участников
        User::where('team_id', $team->id)->update(['team_id' => null]);
        
        $team->delete();

        return back()->with('success', 'Команда успешно удалена.');
    }

    /**
     * Создать пользователя
     */
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users|min:3|max:50',
            'password' => 'required|string|min:6',
            'surname' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users|max:120',
            'role' => 'required|in:admin,captain,participant',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        
        User::create($validated);

        return back()->with('success', 'Пользователь успешно создан.');
    }

    /**
     * Удалить пользователя
     */
    public function destroyUser(User $user)
    {
        // Проверка на удаление последнего администратора
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Невозможно удалить последнего администратора.');
        }

        // Если пользователь - капитан команды
        if ($user->isCaptain() && $user->team_id) {
            // Находим нового капитана среди участников команды
            $newCaptain = User::where('team_id', $user->team_id)
                ->where('id', '!=', $user->id)
                ->where('role', '!=', 'admin')
                ->first();
            
            if ($newCaptain) {
                $newCaptain->update(['role' => 'captain']);
                
                // Обновляем команду
                Team::where('id', $user->team_id)
                    ->update(['captain_id' => $newCaptain->id]);
            } else {
                // Если нет других участников, удаляем команду
                Team::where('id', $user->team_id)->delete();
            }
        }

        // Удаляем пользователя
        $user->delete();

        return back()->with('success', 'Пользователь успешно удален.');
    }
}
