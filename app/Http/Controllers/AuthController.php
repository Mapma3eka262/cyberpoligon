<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\TeamInvitationMail;

class AuthController extends Controller
{
    /**
     * Показать страницу входа/регистрации
     */
    public function showLoginRegister()
    {
        return view('auth.login-register');
    }

    /**
     * Обработка входа
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Проверка временного пароля
        $user = User::where('username', $credentials['username'])->first();
        
        if ($user && $user->temporary_password) {
            if (Hash::check($credentials['password'], $user->temporary_password)) {
                // Первый вход с временным паролем
                Auth::login($user);
                
                if ($user->role === 'participant') {
                    return redirect()->route('dashboard')->with('success', 'Добро пожаловать! Пожалуйста, смените временный пароль.');
                }
                
                return redirect()->route('dashboard');
            }
        }

        // Стандартная аутентификация
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.teams');
            }
            
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'username' => 'Неверные учетные данные.',
        ]);
    }

    /**
     * Обработка регистрации
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users|min:3|max:50',
            'password' => 'required|string|min:6|confirmed',
            'surname' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users|max:120',
            'team_name' => [
                'required_if:role,captain',
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($value && Team::where('name', $value)->exists()) {
                        $fail('Команда с таким названием уже существует.');
                    }
                },
            ],
            'role' => 'required|in:captain,participant',
            'agree_pd' => 'required|accepted',
        ]);

        // Создаем пользователя
        $user = User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'surname' => $validated['surname'],
            'name' => $validated['name'],
            'patronymic' => $validated['patronymic'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // Если капитан - создаем команду
        if ($validated['role'] === 'captain') {
            if (empty($validated['team_name'])) {
                return back()->withErrors([
                    'team_name' => 'Название команды обязательно для капитана.'
                ])->withInput();
            }
            
            $team = Team::create([
                'name' => $validated['team_name'],
                'captain_id' => $user->id,
            ]);
            
            $user->team_id = $team->id;
            $user->save();
        }

        Auth::login($user);
        
        if ($user->isCaptain()) {
            return redirect()->route('dashboard')->with('success', 'Команда создана! Теперь вы можете добавить участников.');
        }
        
        return redirect()->route('dashboard')->with('success', 'Регистрация успешна!');
    }

    /**
     * Регистрация участника капитаном
     */
    public function registerParticipant(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isCaptain()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен.'], 403);
        }

        $validated = $request->validate([
            'surname' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users|max:120',
        ]);

        // Генерируем временные данные
        $temporaryPassword = Str::random(12);
        $username = Str::slug($validated['name'] . '_' . $validated['surname']) . '_' . rand(100, 999);

        // Создаем участника
        $participant = User::create([
            'username' => $username,
            'password' => Hash::make(Str::random(32)),
            'temporary_password' => Hash::make($temporaryPassword),
            'surname' => $validated['surname'],
            'name' => $validated['name'],
            'patronymic' => $validated['patronymic'],
            'email' => $validated['email'],
            'role' => 'participant',
            'team_id' => Auth::user()->team_id,
        ]);

        // Отправляем email с временным паролем
        try {
            Mail::to($validated['email'])->send(new TeamInvitationMail(
                $validated['name'],
                $temporaryPassword,
                Auth::user()->team->name
            ));
        } catch (\Exception $e) {
            \Log::error('Ошибка отправки email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Участник зарегистрирован. На его email отправлены данные для входа.'
        ]);
    }

    /**
     * Добавление существующего участника в команду
     */
    public function addExistingParticipant(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isCaptain()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($validated['user_id']);
        $captain = Auth::user();

        // Проверяем, что пользователь не админ и не капитан
        if ($user->isAdmin() || $user->isCaptain()) {
            return response()->json([
                'success' => false, 
                'message' => 'Нельзя добавить администратора или капитана в команду.'
            ], 422);
        }

        // Проверяем, что пользователь уже не в команде
        if ($user->team_id) {
            return response()->json([
                'success' => false, 
                'message' => 'Этот пользователь уже состоит в команде.'
            ], 422);
        }

        // Проверяем, что пользователь не является самим капитаном
        if ($user->id === $captain->id) {
            return response()->json([
                'success' => false, 
                'message' => 'Нельзя добавить самого себя.'
            ], 422);
        }

        // Добавляем в команду
        $user->team_id = $captain->team_id;
        
        // Генерируем новый временный пароль для существующего пользователя
        $temporaryPassword = Str::random(12);
        $user->temporary_password = Hash::make($temporaryPassword);
        $user->save();

        // Отправляем email с приглашением
        try {
            Mail::to($user->email)->send(new TeamInvitationMail(
                $user->name,
                $temporaryPassword,
                $captain->team->name
            ));
        } catch (\Exception $e) {
            \Log::error('Ошибка отправки email существующему пользователю: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Участник добавлен в команду. На его email отправлены данные для входа.'
        ]);
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', 'Вы успешно вышли из системы.');
    }
}
