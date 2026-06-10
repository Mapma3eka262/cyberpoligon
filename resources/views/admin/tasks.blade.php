@extends('layouts.app')

@section('title', 'Управление заданиями')

@section('styles')
<style>
    .flag-preview {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-top: 5px;
    }
    .task-status-active {
        color: var(--success-color);
    }
    .task-status-inactive {
        color: var(--danger-color);
    }
    .osint-badge {
        background-color: #6f42c1;
        color: white;
    }
    .ctf-badge {
        background-color: #0d6efd;
        color: white;
    }
    .answer-row {
        background: #f8f9fa;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }
    .hint-row {
        background: #fff3cd;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }
</style>
@endsection

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tasks me-2"></i>Управление заданиями</h2>
        <button type="button" class="btn btn-ctf" data-bs-toggle="modal" data-bs-target="#createTaskModal">
            <i class="bi bi-plus-circle me-2"></i>Создать задание
        </button>
    </div>

    <!-- Таблица заданий -->
    <div class="card task-card">
        <div class="task-card-header">
            <h4 class="mb-0">Список заданий</h4>
        </div>
        <div class="card-body">
            @if($tasks->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tasks display-1"></i>
                    <h4 class="mt-3">Нет заданий</h4>
                    <p>Создайте первое задание, нажав кнопку "Создать задание"</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-ctf table-hover">
                        <thead>
                            <tr>
                                <th>Тип</th>
                                <th>Название</th>
                                <th>Целевой IP</th>
                                <th>Время (мин)</th>
                                <th>Назначено</th>
                                <th>Статус</th>
                                <th>Дата создания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                            <tr>
                                <td>
                                    @if($task->type === 'osint')
                                        <span class="badge osint-badge">OSINT</span>
                                    @else
                                        <span class="badge ctf-badge">CTF</span>
                                    @endif
                                </td>
                                <td class="text-muted">
                                    <strong>{{ $task->name }}</strong>
                                    @if($task->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($task->type === 'ctf')
                                        <code>{{ $task->target_ip }}</code>
                                        @if($task->subnet_mask)
                                            <br>
                                            <small class="text-muted">{{ $task->subnet_mask }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $task->time_limit_minutes }}</td>
                                <td>
                                    @if($task->type === 'ctf')
                                        <span class="badge bg-primary">{{ $task->teams->count() }} команд</span>
                                    @else
                                        <span class="badge bg-info">{{ $task->userTasks->count() }} участников</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->is_active)
                                        <span class="task-status-active">
                                            <i class="bi bi-check-circle"></i> Активно
                                        </span>
                                    @else
                                        <span class="task-status-inactive">
                                            <i class="bi bi-x-circle"></i> Неактивно
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $task->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" data-bs-target="#editTaskModal{{ $task->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" data-bs-target="#viewTaskModal{{ $task->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete({{ $task->id }}, '{{ $task->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $task->id }}" 
                                          action="{{ route('admin.tasks.destroy', $task) }}" 
                                          method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if($tasks->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $tasks->links() }}
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно создания задания -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.tasks.store') }}" id="createTaskForm">
                @csrf
                <div class="modal-header">
                    <h5 class="text-black modal-title" id="createTaskModalLabel">Создать задание</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <!-- Выбор типа задания -->
                    <div class="mb-4">
                        <label class="text-black form-label fw-bold">Тип задания *</label>
                        <div class="mt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeCtf" value="ctf" checked>
                                <label class="form-check-label" for="typeCtf">CTF (командное)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeOsint" value="osint">
                                <label class="form-check-label" for="typeOsint">OSINT (индивидуальное)</label>
                            </div>
                        </div>
                    </div>

                    <!-- Общие поля -->
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="task_name" class="text-black form-label">Название *</label>
                            <input type="text" class="form-control form-control-ctf" id="task_name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="time_limit_minutes" class="text-black form-label">Время (минут)</label>
                            <input type="number" class="form-control form-control-ctf" id="time_limit_minutes" name="time_limit_minutes" min="0" value="60" placeholder="0 = без ограничений">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="text-black form-label">Описание</label>
                        <textarea class="form-control form-control-ctf" id="description" name="description" rows="3"></textarea>
                    </div>

                    <!-- CTF-специфичные поля -->
                    <div id="ctfFields">
                        <hr>
                        <h5 class="text-black">CTF-специфичные настройки</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_ip" class="text-black form-label">IP адрес *</label>
                                <input type="text" class="form-control form-control-ctf" id="target_ip" name="target_ip" placeholder="192.168.1.100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subnet_mask" class="text-black form-label">Подсеть</label>
                                <input type="text" class="form-control form-control-ctf" id="subnet_mask" name="subnet_mask" placeholder="255.255.255.0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vm_id" class="text-black form-label">ID виртуальной машины (Proxmox)</label>
                                <input type="number" class="form-control form-control-ctf" id="vm_id" name="vm_id" placeholder="100" min="100" max="9999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="proxmox_node" class="text-black form-label">Нода Proxmox</label>
                                <input type="text" class="form-control form-control-ctf" id="proxmox_node" name="proxmox_node" placeholder="pve" value="pve">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="flag1_value" class="text-black form-label">Флаг 1 *</label>
                                <input type="text" class="form-control form-control-ctf" id="flag1_value" name="flag1_value" placeholder="CTF{first_flag_example}">
                                <div class="form-text text-black">30 баллов</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="flag2_value" class="text-black form-label">Флаг 2 *</label>
                                <input type="text" class="form-control form-control-ctf" id="flag2_value" name="flag2_value" placeholder="CTF{second_flag_example}">
                                <div class="form-text text-black">70 баллов</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="teams" class="text-black form-label">Назначить командам</label>
                            <select class="form-select form-control-ctf" id="teams" name="teams[]" multiple>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text text-black">Удерживайте Ctrl для выбора нескольких команд</div>
                        </div>
                    </div>

                    <!-- OSINT-специфичные поля -->
                    <div id="osintFields" style="display: none;">
                        <hr>
                        <h5 class="text-black">OSINT-специфичные настройки</h5>

                        <div class="mb-3">
                            <label class="text-black form-label">Источники (источники данных для поиска)</label>
                            <div class="row">
                                @php
                                    $sources = ['github' => 'GitHub', 'twitter' => 'Twitter/X', 'whois' => 'WHOIS', 'dns' => 'DNS', 'shodan' => 'Shodan', 'google' => 'Google Dorks', 'telegram' => 'Telegram', 'other' => 'Другое'];
                                @endphp
                                @foreach($sources as $key => $label)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="osint_sources[]" value="{{ $key }}" id="source_{{ $key }}">
                                        <label class="form-check-label" for="source_{{ $key }}">{{ $label }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="text-black form-label">Максимальный балл</label>
                            <input type="number" class="form-control form-control-ctf" name="osint_max_score" value="100" min="1">
                        </div>

                        <div class="mb-3">
                            <label class="text-black form-label fw-bold">Ожидаемые ответы</label>
                            <div id="answersContainer">
                                <div class="answer-row">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="osint_expected_answers[0][key]" placeholder="Ключ (например: github_username)">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="osint_expected_answers[0][description]" placeholder="Описание">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="osint_expected_answers[0][answer]" placeholder="Правильный ответ">
                                        </div>
                                        <div class="col-md-1">
                                            <input type="number" class="form-control" name="osint_points_per_answer[0]" placeholder="Баллы" value="10">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addAnswerRow()">
                                <i class="bi bi-plus"></i> Добавить вопрос
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="text-black form-label fw-bold">Подсказки (со штрафом)</label>
                            <div id="hintsContainer">
                                <div class="hint-row">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="osint_hints[0][text]" placeholder="Текст подсказки">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" class="form-control" name="osint_hints[0][cost]" placeholder="Штраф в баллах" value="5">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addHintRow()">
                                <i class="bi bi-plus"></i> Добавить подсказку
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="users" class="text-black form-label">Назначить участникам</label>
                            <select class="form-select form-control-ctf" id="users" name="users[]" multiple>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->surname }} {{ $user->name }} ({{ $user->username }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-black">Удерживайте Ctrl для выбора нескольких участников</div>
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

<!-- Модальные окна просмотра и редактирования для каждого задания -->
@foreach($tasks as $task)
<!-- Просмотр -->
<div class="modal fade" id="viewTaskModal{{ $task->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-black modal-title">Задание: {{ $task->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Тип:</strong> {{ $task->type === 'osint' ? 'OSINT' : 'CTF' }}</p>
                <p><strong>Статус:</strong> {!! $task->is_active ? '<span class="badge bg-success">Активно</span>' : '<span class="badge bg-secondary">Неактивно</span>' !!}</p>
                <p><strong>Описание:</strong> {{ $task->description ?? 'Нет' }}</p>
                
                @if($task->type === 'ctf')
                    <p><strong>IP:</strong> {{ $task->target_ip }}</p>
                    <p><strong>Время:</strong> {{ $task->time_limit_minutes }} минут</p>
                    <p><strong>Команды:</strong> {{ $task->teams->count() }}</p>
                @else
                    <p><strong>Макс. балл:</strong> {{ json_decode($task->osint_data, true)['max_score'] ?? 100 }}</p>
                    <p><strong>Участников:</strong> {{ $task->userTasks->count() }}</p>
                    @php $osintData = json_decode($task->osint_data, true); @endphp
                    @if(!empty($osintData['sources']))
                        <p><strong>Источники:</strong> {{ implode(', ', $osintData['sources']) }}</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Редактирование -->
<div class="modal fade" id="editTaskModal{{ $task->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.tasks.update', $task) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="text-black modal-title">Редактировать: {{ $task->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="text-black form-label">Название</label>
                        <input type="text" class="form-control" name="name" value="{{ $task->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="text-black form-label">Описание</label>
                        <textarea class="form-control" name="description" rows="3">{{ $task->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-black form-label">Время (минут)</label>
                            <input type="number" class="form-control" name="time_limit_minutes" value="{{ $task->time_limit_minutes }}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $task->is_active ? 'checked' : '' }}>
                                <label class="form-check-label">Активно</label>
                            </div>
                        </div>
                    </div>
                    @if($task->type === 'ctf')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-black form-label">IP адрес</label>
                            <input type="text" class="form-control" name="target_ip" value="{{ $task->target_ip }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-black form-label">Подсеть</label>
                            <input type="text" class="form-control" name="subnet_mask" value="{{ $task->subnet_mask }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-black form-label">VM ID</label>
                            <input type="number" class="form-control" name="vm_id" value="{{ $task->vm_id }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-black form-label">Нода Proxmox</label>
                            <input type="text" class="form-control" name="proxmox_node" value="{{ $task->proxmox_node }}">
                        </div>
                    </div>
                    @endif
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

@endsection

@section('scripts')
<script>
let answerCounter = 1;
let hintCounter = 1;

function addAnswerRow() {
    answerCounter++;
    const container = document.getElementById('answersContainer');
    const newRow = document.createElement('div');
    newRow.className = 'answer-row';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" name="osint_expected_answers[${answerCounter}][key]" placeholder="Ключ">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="osint_expected_answers[${answerCounter}][description]" placeholder="Описание">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="osint_expected_answers[${answerCounter}][answer]" placeholder="Правильный ответ">
            </div>
            <div class="col-md-1">
                <input type="number" class="form-control" name="osint_points_per_answer[${answerCounter}]" placeholder="Баллы" value="10">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger mt-1" onclick="this.parentElement.remove()">Удалить</button>
    `;
    container.appendChild(newRow);
}

function addHintRow() {
    hintCounter++;
    const container = document.getElementById('hintsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'hint-row';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-8">
                <input type="text" class="form-control" name="osint_hints[${hintCounter}][text]" placeholder="Текст подсказки">
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="osint_hints[${hintCounter}][cost]" placeholder="Штраф" value="5">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger mt-1" onclick="this.parentElement.remove()">Удалить</button>
    `;
    container.appendChild(newRow);
}

document.addEventListener('DOMContentLoaded', function() {
    const typeCtf = document.getElementById('typeCtf');
    const typeOsint = document.getElementById('typeOsint');
    const ctfFields = document.getElementById('ctfFields');
    const osintFields = document.getElementById('osintFields');

    function toggleFields() {
        if (typeOsint.checked) {
            ctfFields.style.display = 'none';
            osintFields.style.display = 'block';
        } else {
            ctfFields.style.display = 'block';
            osintFields.style.display = 'none';
        }
    }

    typeCtf.addEventListener('change', toggleFields);
    typeOsint.addEventListener('change', toggleFields);
    toggleFields();

    if (typeof Choices !== 'undefined') {
        new Choices('#teams', { removeItemButton: true, searchEnabled: true });
        new Choices('#users', { removeItemButton: true, searchEnabled: true });
    }
});

function confirmDelete(taskId, taskName) {
    if (confirm(`Удалить задание "${taskName}"?`)) {
        document.getElementById(`delete-form-${taskId}`).submit();
    }
}
</script>
@endsection