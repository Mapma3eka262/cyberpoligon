<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Показать личный кабинет
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->team;
        $members = $team ? $team->members()->where('id', '!=', $user->id)->get() : collect();
        
        // Получаем список пользователей без команды (участников)
        $availableUsers = collect();
        if ($user->isCaptain() && $team) {
            $availableUsers = User::where('role', 'participant')
                ->whereNull('team_id')
                ->where('id', '!=', $user->id)
                ->orderBy('surname')
                ->orderBy('name')
                ->get();
        }
        
        return view('dashboard', [
            'user' => $user,
            'team' => $team,
            'members' => $members,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Обновить контактные данные
     */
    public function updateContactInfo(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:120|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->update($validated);

        return back()->with('success', 'Контактные данные успешно обновлены.');
    }

    /**
     * Сменить пароль
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Проверка текущего пароля
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Текущий пароль неверен.']);
        }

        // Обновляем пароль и очищаем временный
        $user->update([
            'password' => Hash::make($validated['new_password']),
            'temporary_password' => null,
        ]);

        return back()->with('success', 'Пароль успешно изменен.');
    }
}
