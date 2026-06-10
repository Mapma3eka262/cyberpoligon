@extends('layouts.app')

@section('title', 'Управление командами')

@section('styles')
<style>
    .team-actions .btn {
        margin-right: 5px;
    }
    .user-actions .btn {
        margin-right: 5px;
    }
    .modal-xl {
        max-width: 800px;
    }
    .user-badge {
        font-size: 0.8rem;
    }
    .modal-body-scrollable {
        max-height: 70vh;
        overflow-y: auto;
    }
    .card-header-tabs {
        border-bottom: none;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="fade-in">
    <!-- Вкладки -->
    <div class="card task-card">
        <div class="card-header card-header-tabs">
            <ul class="nav nav-tabs card-header-tabs" id="managementTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams" type="button" role="tab">
                        <i class="bi bi-people-fill me-2"></i>Команды
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                        <i class="bi bi-person-fill me-2"></i>Пользователи
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content" id="managementTabsContent">
                <!-- Вкладка команд -->
                <div class="tab-pane fade show active" id="teams" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><p class="bi bi-people-fill me-2"></i><p class="text-muted">Управление командами</p></h4>
                        <button type="button" class="btn btn-ctf" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                            <i class="bi bi-plus-circle me-2"></i>Создать команду
                        </button>
                    </div>

                    <!-- Таблица команд -->
                    <div class="card task-card">
                        <div class="task-card-header">
                            <h5 class="mb-0">Список команд</h5>
                        </div>
                        <div class="card-body">
                            @if($teams->isEmpty())
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-1"></i>
                                    <h4 class="mt-3">Нет команд</h4>
                                    <p>Создайте первую команду, нажав кнопку "Создать команду"</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-ctf table-hover">
                                        <thead>
                                            <tr>
                                                <th>Название</th>
                                                <th>Капитан</th>
                                                <th>Участников</th>
                                                <th>Активных заданий</th>
                                                <th>Баллов</th>
                                                <th>Дата создания</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($teams as $team)
                                            <tr>
                                                <td class="text-muted">
                                                    <strong>{{ $team->name }}</strong>
                                                </td>
                                                <td class="text-muted">
                                                    @if($team->captain)
                                                        {{ $team->captain->surname }} {{ $team->captain->name }}
                                                        <br>
                                                        <small class="text-muted">{{ $team->captain->email }}</small>
                                                    @else
                                                        <span class="text-danger">Нет капитана</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $team->members->count() }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $activeTasks = $team->tasks()->wherePivot('end_time', '>', now())->count();
                                                    @endphp
                                                    <span class="badge bg-{{ $activeTasks > 0 ? 'success' : 'secondary' }}">
                                                        {{ $activeTasks }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="text-primary">{{ $team->tasks()->sum('score') }}</strong>
                                                </td>
                                                <td class="text-muted">{{ $team->created_at->format('d.m.Y H:i') }}</td>
                                                <td class="team-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#editTeamModal{{ $team->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" data-bs-target="#viewTeamModal{{ $team->id }}">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete({{ $team->id }}, '{{ $team->name }}')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $team->id }}" 
                                                          action="{{ route('admin.teams.destroy', $team) }}" 
                                                          method="POST" class="d-none">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    
                                    <!-- Пагинация -->
                                    @if($teams->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $teams->links() }}
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Вкладка пользователей -->
                <div class="tab-pane fade" id="users" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="bi bi-person-fill me-2"></i><p class="text-muted">Управление пользователями</p></h4>
                        <div>
                            <button type="button" class="btn btn-ctf" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                <i class="bi bi-person-plus me-2"></i>Создать пользователя
                            </button>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                <i class="bi bi-person-dash me-2"></i>Удалить пользователя
                            </button>
                        </div>
                    </div>

                    <!-- Таблица пользователей -->
                    <div class="card task-card">
                        <div class="task-card-header">
                            <h5 class="mb-0">Список пользователей</h5>
                        </div>
                        <div class="card-body">
                            @if($users->isEmpty())
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-person display-1"></i>
                                    <h4 class="mt-3">Нет пользователей</h4>
                                    <p>Создайте первого пользователя</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-ctf table-hover">
                                        <thead>
                                            <tr>
                                                <th>Логин</th>
                                                <th>ФИО</th>
                                                <th>Email</th>
                                                <th>Роль</th>
                                                <th>Команда</th>
                                                <th>Дата регистрации</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($users as $user)
                                            <tr>
                                                <td class="text-muted">
                                                    <strong>{{ $user->username }}</strong>
                                                    @if($user->isAdmin())
                                                        <span class="badge bg-danger ms-1">Admin</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted">{{ $user->surname }} {{ $user->name }}</td>
                                                <td class="text-muted">{{ $user->email }}</td>
                                                <td>
                                                    @if($user->isAdmin())
                                                        <span class="badge bg-danger">Администратор</span>
                                                    @elseif($user->isCaptain())
                                                        <span class="badge bg-warning">Капитан</span>
                                                    @else
                                                        <span class="badge bg-secondary">Участник</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->team)
                                                        <span class="badge bg-primary">{{ $user->team->name }}</span>
                                                    @else
                                                        <span class="badge bg-light text-dark">Нет команды</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted">{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                                <td>
                                                    @if($user->temporary_password)
                                                        <span class="badge bg-warning">Ожидает активации</span>
                                                    @else
                                                        <span class="badge bg-success">Активен</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    
                                    <!-- Пагинация для пользователей -->
                                    @if($users->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $users->links() }}
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна для команд (остаются без изменений) -->
@foreach($teams as $team)
<!-- Модальное окно просмотра команды -->
<div class="modal fade" id="viewTeamModal{{ $team->id }}" tabindex="-1" aria-labelledby="viewTeamModalLabel{{ $team->id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-black modal-title" id="viewTeamModalLabel{{ $team->id }}">Команда: {{ $team->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body modal-body-scrollable">
                <h6 class="text-black">Участники:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ФИО</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($team->members as $member)
                            <tr>
                                <td>{{ $member->surname }} {{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    @if($member->id == $team->captain_id)
                                        <span class="badge bg-warning">Капитан</span>
                                    @else
                                        <span class="badge bg-secondary">Участник</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->temporary_password)
                                        <span class="badge bg-warning">Ожидает активации</span>
                                    @else
                                        <span class="badge bg-success">Активен</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <h6 class="mt-4 text-black">Активные задания:</h6>
                @php
                    $activeTasks = $team->tasks()->wherePivot('end_time', '>', now())->get();
                @endphp
                @if($activeTasks->isEmpty())
                    <p class="text-muted">Нет активных заданий</p>
                @else
                    <div class="row">
                        @foreach($activeTasks as $task)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6>{{ $task->name }}</h6>
                                    <p class="small mb-1">
                                        <strong>IP:</strong> {{ $task->target_ip }}
                                    </p>
                                    <p class="small mb-1">
                                        <strong>Прогресс:</strong>
                                        @if($task->pivot->flag1_found && $task->pivot->flag2_found)
                                            <span class="text-success">✅ Все флаги</span>
                                        @elseif($task->pivot->flag1_found || $task->pivot->flag2_found)
                                            <span class="text-warning">⏳ В процессе</span>
                                        @else
                                            <span class="text-danger">❌ Не начато</span>
                                        @endif
                                    </p>
                                    <p class="small mb-0">
                                        <strong>Баллы:</strong> {{ $task->pivot->score }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования команды -->
<div class="modal fade" id="editTeamModal{{ $team->id }}" tabindex="-1" aria-labelledby="editTeamModalLabel{{ $team->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.teams.update', $team) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="text-black modal-title" id="editTeamModalLabel{{ $team->id }}">Редактировать команду</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name{{ $team->id }}" class="text-black form-label">Название команды</label>
                        <input type="text" class="form-control form-control-ctf" 
                               id="name{{ $team->id }}" name="name" 
                               value="{{ $team->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="captain_id{{ $team->id }}" class="text-black form-label">Капитан</label>
                        <select class="form-select form-control-ctf" 
                                id="captain_id{{ $team->id }}" name="captain_id" required>
                            @foreach($team->members as $member)
                            <option value="{{ $member->id }}" 
                                    {{ $member->id == $team->captain_id ? 'selected' : '' }}>
                                {{ $member->surname }} {{ $member->name }} ({{ $member->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-ctf">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Модальное окно создания команды -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.teams.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="text-black modal-title" id="createTeamModalLabel">Создать команду</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="team_name" class="text-black form-label">Название команды *</label>
                        <input type="text" class="form-control form-control-ctf" 
                               id="team_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="captain_id" class="text-black form-label">Капитан *</label>
                        <select class="form-select form-control-ctf" id="captain_id" name="captain_id" required>
                            <option value="">Выберите капитана</option>
                            @foreach($users as $user)
                                @if($user->team_id === null && $user->role !== 'admin')
                                    <option value="{{ $user->id }}">
                                        {{ $user->surname }} {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">
                            Пользователь должен быть зарегистрирован в системе
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-ctf">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно создания пользователя -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.teams.create-user') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="text-black modal-title" id="createUserModalLabel">Создать пользователя</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="text-black form-label">Логин *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="text-black form-label">Пароль *</label>
                            <input type="password" class="form-control form-control-ctf" 
                                   id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="surname" class="text-black form-label">Фамилия *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="surname" name="surname" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="name" class="text-black form-label">Имя *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="text-black form-label">Email *</label>
                            <input type="email" class="form-control form-control-ctf" 
                                   id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="text-black form-label">Роль *</label>
                            <select class="form-select form-control-ctf" id="role" name="role" required>
                                <option value="participant">Участник</option>
                                <option value="captain">Капитан</option>
                                <option value="admin">Администратор</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="team_id" class="text-black form-label">Команда</label>
                            <select class="form-select form-control-ctf" id="team_id" name="team_id">
                                <option value="">Без команды</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-ctf-success">
                        <i class="bi bi-person-plus me-2"></i>Создать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно удаления пользователя -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="text-black modal-title" id="deleteUserModalLabel">
                        <i class="bi bi-person-dash text-danger me-2"></i>Удаление пользователя
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Внимание!</strong> Это действие необратимо. Удаленный пользователь не сможет войти в систему.
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_to_delete" class="text-black form-label">Выберите пользователя *</label>
                        <select class="form-select form-control-ctf" id="user_to_delete" name="user_id" required>
                            <option value="">Выберите пользователя...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" data-role="{{ $user->role }}">
                                    {{ $user->surname }} {{ $user->name }}
                                    ({{ $user->username }}) - 
                                    @if($user->isAdmin())
                                        <span class="text-danger">Администратор</span>
                                    @elseif($user->isCaptain())
                                        <span class="text-warning">Капитан</span>
                                    @else
                                        <span class="text-secondary">Участник</span>
                                    @endif
                                    @if($user->team)
                                        | Команда: {{ $user->team->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div id="userInfo" class="mt-3 p-3 border rounded" style="display: none;">
                            <h6 class="text-black">Информация о пользователе:</h6>
                            <p id="userDetails" class="mb-1 text-black"></p>
                            <p id="userRole" class="mb-1"></p>
                            <p id="userTeam" class="mb-1 text-black"></p>
                            <p id="userRegistration" class="mb-0 text-black"></p>
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                        <label class="form-check-label text-black" for="confirmDelete">
                            Я понимаю, что удаление пользователя является необратимым действием
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger" id="submitDeleteBtn">
                        <i class="bi bi-trash me-2"></i>Удалить пользователя
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(teamId, teamName) {
    if (confirm(`Удалить команду "${teamName}"?\n\nВсе участники будут удалены из команды.`)) {
        document.getElementById(`delete-form-${teamId}`).submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Загрузка пользователей для выбора капитана
    const captainSelect = document.getElementById('captain_id');
    if (captainSelect) {
        // Код оставлен для будущей реализации AJAX-загрузки
    }

    // Обработка выбора пользователя для удаления
    const userSelect = document.getElementById('user_to_delete');
    const userInfo = document.getElementById('userInfo');
    const userDetails = document.getElementById('userDetails');
    const userRole = document.getElementById('userRole');
    const userTeam = document.getElementById('userTeam');
    const userRegistration = document.getElementById('userRegistration');
    const deleteForm = document.getElementById('deleteUserForm');
    const submitDeleteBtn = document.getElementById('submitDeleteBtn');
    
    // Данные пользователей из PHP
    const usersData = {!! json_encode($users->keyBy('id')->map(function($user) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'surname' => $user->surname,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_text' => $user->isAdmin() ? 'Администратор' : ($user->isCaptain() ? 'Капитан' : 'Участник'),
            'team_name' => $user->team ? $user->team->name : 'Нет команды',
            'created_at' => $user->created_at ? $user->created_at->format('d.m.Y H:i') : '',
            'is_admin' => $user->isAdmin()
        ];
    })) !!};

    if (userSelect) {
        userSelect.addEventListener('change', function() {
            const userId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            
            if (userId && usersData[userId]) {
                const user = usersData[userId];
                
                userDetails.textContent = `ФИО: ${user.surname} ${user.name}`;
                userRole.textContent = `Роль: ${user.role_text}`;
                userRole.className = `mb-1 ${user.is_admin ? 'text-danger' : (user.role === 'captain' ? 'text-warning' : 'text-secondary')}`;
                userTeam.textContent = `Команда: ${user.team_name}`;
                userRegistration.textContent = `Дата регистрации: ${user.created_at}`;
                
                userInfo.style.display = 'block';
                
                // Устанавливаем action формы
                deleteForm.action = `{{ route('admin.teams.destroy-user', '') }}/${userId}`;
                
                // Если это администратор, предупреждаем
                if (user.is_admin) {
                    submitDeleteBtn.disabled = false;
                    submitDeleteBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Удалить администратора';
                } else {
                    submitDeleteBtn.disabled = false;
                    submitDeleteBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Удалить пользователя';
                }
            } else {
                userInfo.style.display = 'none';
                submitDeleteBtn.disabled = true;
            }
        });
    }

    // Обработка отправки формы удаления
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            const userId = document.getElementById('user_to_delete').value;
            const user = usersData[userId];
            
            if (user) {
                const message = user.is_admin 
                    ? `Вы собираетесь удалить администратора:\n\n${user.surname} ${user.name} (${user.username})\n\nЭто действие может ограничить доступ к административным функциям.\n\nПродолжить?`
                    : `Вы уверены, что хотите удалить пользователя:\n\n${user.surname} ${user.name} (${user.username})?\n\nЭто действие необратимо.`;
                
                if (!confirm(message)) {
                    e.preventDefault();
                }
            }
        });
    }

    // Активация вкладки при загрузке страницы с хэшем
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const tabTrigger = document.querySelector(`[data-bs-target="#${hash}"]`);
        if (tabTrigger) {
            new bootstrap.Tab(tabTrigger).show();
        }
    }
    
    // Сохранение активной вкладки в URL
    const tabTriggers = document.querySelectorAll('#managementTabs button[data-bs-toggle="tab"]');
    tabTriggers.forEach(tabTrigger => {
        tabTrigger.addEventListener('click', function(e) {
            const target = this.getAttribute('data-bs-target');
            if (target) {
                window.location.hash = target.substring(1);
            }
        });
    });
});
</script>
@endsection
