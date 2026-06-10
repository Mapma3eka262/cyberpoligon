@extends('layouts.app')

@section('title', 'Статистика')

@section('styles')
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .progress-thin {
        height: 8px;
        border-radius: 4px;
    }
    .team-progress {
        position: relative;
        height: 30px;
        background: #f0f0f0;
        border-radius: 5px;
        overflow: hidden;
    }
    .flag-progress {
        position: absolute;
        height: 100%;
        transition: width 0.5s ease;
    }
    .flag-1-progress {
        background: linear-gradient(90deg, #4CAF50, #8BC34A);
    }
    .flag-2-progress {
        background: linear-gradient(90deg, #2196F3, #03A9F4);
    }
    .team-name {
        position: relative;
        z-index: 2;
        padding: 0 10px;
        line-height: 30px;
        color: #333;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart me-2"></i>Статистика</h2>
        <div>
            <a href="{{ route('admin.stats.export') ?? '#' }}" class="btn btn-ctf-success">
                <i class="bi bi-download me-2"></i>Экспорт CSV
            </a>
        </div>
    </div>

    <!-- Общая статистика -->
    @php
        // Защитные значения по умолчанию
        $totalStats = $totalStats ?? [
            'total_teams' => 0,
            'active_teams' => 0,
            'total_tasks' => 0,
            'active_tasks' => 0,
            'total_flag_attempts' => 0,
            'correct_attempts' => 0,
            'success_rate' => 0
        ];
        $teamStats = $teamStats ?? [];
    @endphp

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-start border-5 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Всего команд</h6>
                            <h3 class="text-primary">{{ $totalStats['total_teams'] }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-black">
                            <i class="bi bi-activity me-1"></i>
                            Активных: {{ $totalStats['active_teams'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-start border-5 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Всего заданий</h6>
                            <h3 class="text-success">{{ $totalStats['total_tasks'] }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="bi bi-flag-fill"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-black">
                            <i class="bi bi-check-circle me-1"></i>
                            Активных: {{ $totalStats['active_tasks'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-start border-5 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Попыток флагов</h6>
                            <h3 class="text-warning">{{ $totalStats['total_flag_attempts'] }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;">
                            <i class="bi bi-send-fill"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-black">
                            <i class="bi bi-check-circle me-1"></i>
                            Верных: {{ $totalStats['correct_attempts'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-start border-5 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Успешность</h6>
                            <h3 class="text-info">{{ $totalStats['success_rate'] }}%</h3>
                        </div>
                        <div class="text-info" style="font-size: 2rem;">
                            <i class="bi bi-percent"></i>
                        </div>
                    </div>
                    <div class="progress progress-thin mt-2">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: {{ min($totalStats['success_rate'], 100) }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика по заданиям -->
    @if(!empty($teamStats) && count($teamStats) > 0)
        @foreach($teamStats as $taskId => $taskData)
        @php
            $task = $taskData['task'] ?? null;
            $teams = $taskData['teams'] ?? collect();
        @endphp
        
        @if($task)
        <div class="card task-card mb-4">
            <div class="task-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">{{ $task->name ?? 'Неизвестное задание' }}</h4>
                    @if($task)
                    <small class="text-muted">
                        {{ $task->target_ip ?? 'N/A' }} • 
                        {{ $task->time_limit_minutes ?? 0 }} минут • 
                        {{ $teams->count() }} команд
                    </small>
                    @endif
                </div>
                <div>
                    @if(isset($task->is_active) && $task->is_active)
                        <span class="badge bg-success">Активно</span>
                    @else
                        <span class="badge bg-secondary">Завершено</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($teams->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Нет команд, выполняющих это задание</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-ctf">
                            <thead>
                                <tr>
                                    <th>Место</th>
                                    <th>Команда</th>
                                    <th>Прогресс</th>
                                    <th>Флаги</th>
                                    <th>Неправильные попытки</th>
                                    <th>Баллы</th>
                                    <th>Оставшееся время</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teams as $index => $team)
                                @php
                                    $team = (array) $team;
                                    $flag1 = $team['flag1_found'] ?? false;
                                    $flag2 = $team['flag2_found'] ?? false;
                                    $progress = $team['progress_percent'] ?? 0;
                                    $wrongAttempts = $team['wrong_attempts'] ?? 0;
                                    $score = $team['score'] ?? 0;
                                    $remainingTime = $team['remaining_time'] ?? '00:00';
                                    $status = $team['status'] ?? 'not_started';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="text-center">
                                            @if($index == 0)
                                                <span class="badge bg-warning" style="font-size: 1.2rem;">🥇</span>
                                            @elseif($index == 1)
                                                <span class="badge bg-secondary" style="font-size: 1.2rem;">🥈</span>
                                            @elseif($index == 2)
                                                <span class="badge bg-danger" style="font-size: 1.2rem;">🥉</span>
                                            @else
                                                <strong>#{{ $index + 1 }}</strong>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <strong>{{ $team['name'] ?? 'Без названия' }}</strong>
                                    </td>
                                    <td>
                                        <div class="team-progress mb-2">
                                            <div class="flag-progress flag-1-progress" 
                                                 style="width: {{ $flag1 ? 50 : 0 }}%"></div>
                                            <div class="flag-progress flag-2-progress" 
                                                 style="width: {{ $flag2 ? 50 : 0 }}%; 
                                                        left: {{ $flag1 ? 50 : 0 }}%"></div>
                                            <div class="team-name">
                                                {{ $progress }}%
                                            </div>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ $progress }}%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <div class="me-3">
                                                @if($flag1)
                                                    <span class="badge bg-success">✅ Флаг 1</span>
                                                @else
                                                    <span class="badge bg-secondary">❌ Флаг 1</span>
                                                @endif
                                            </div>
                                            <div>
                                                @if($flag2)
                                                    <span class="badge bg-success">✅ Флаг 2</span>
                                                @else
                                                    <span class="badge bg-secondary">❌ Флаг 2</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $wrongAttempts > 5 ? 'danger' : ($wrongAttempts > 0 ? 'warning' : 'success') }}">
                                            {{ $wrongAttempts }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $score }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $remainingTime == '00:00' ? 'danger' : 'info' }}">
                                            {{ $remainingTime }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($status)
                                            @case('completed')
                                                <span class="badge bg-success">✅ Завершено</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge bg-warning">⏳ В процессе</span>
                                                @break
                                            @case('timeout')
                                                <span class="badge bg-danger">⏰ Время вышло</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">❌ Не начато</span>
                                        @endswitch
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    @else
        <div class="card task-card mb-4">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-bar-chart display-1"></i>
                <h4 class="mt-3">Нет данных для статистики</h4>
                <p>Создайте задания и начните соревнование, чтобы увидеть статистику</p>
            </div>
        </div>
    @endif

    <!-- Упрощенный общий рейтинг команд -->
    <div class="card task-card">
        <div class="task-card-header">
            <h4 class="mb-0">
                <i class="bi bi-trophy-fill me-2"></i>Общий рейтинг команд
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-ctf">
                    <thead>
                        <tr>
                            <th>Место</th>
                            <th>Команда</th>
                            <th>Капитан</th>
                            <th>Общий счёт</th>
                            <th>Рейтинг</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // УПРОЩЕННЫЙ КОД БЕЗ ОШИБОК
                            $teamsStats = [];
                            
                            try {
                                // Пытаемся получить данные безопасно
                                if (class_exists('\App\Models\Team')) {
                                    $teams = \App\Models\Team::with('captain')->get();
                                    
                                    foreach ($teams as $team) {
                                        // Простая логика без сложных отношений
                                        $totalScore = 0;
                                        try {
                                            if (method_exists($team, 'tasks')) {
                                                $totalScore = $team->tasks()->sum('score') ?? 0;
                                            }
                                        } catch (Exception $e) {
                                            $totalScore = 0;
                                        }
                                        
                                        $teamsStats[] = [
                                            'team' => $team,
                                            'total_score' => $totalScore,
                                        ];
                                    }
                                    
                                    // Сортировка по баллам
                                    usort($teamsStats, function($a, $b) {
                                        return $b['total_score'] <=> $a['total_score'];
                                    });
                                }
                            } catch (Exception $e) {
                                // Если возникла ошибка - оставляем пустой массив
                                $teamsStats = [];
                            }
                        @endphp
                        
                        @if(count($teamsStats) > 0)
                            @foreach($teamsStats as $index => $teamStat)
                            @php
                                $team = $teamStat['team'];
                                $score = $teamStat['total_score'];
                                $maxScore = !empty($teamsStats) ? max(array_column($teamsStats, 'total_score')) : 1;
                                $rating = $maxScore > 0 ? ($score / $maxScore) * 5 : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="text-center">
                                        @if($index == 0)
                                            <span class="badge bg-warning" style="font-size: 1.5rem; padding: 8px 12px;">🥇</span>
                                        @elseif($index == 1)
                                            <span class="badge bg-secondary" style="font-size: 1.5rem; padding: 8px 12px;">🥈</span>
                                        @elseif($index == 2)
                                            <span class="badge bg-danger" style="font-size: 1.5rem; padding: 8px 12px;">🥉</span>
                                        @else
                                            <strong style="font-size: 1.2rem;">#{{ $index + 1 }}</strong>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-muted">
                                    <strong>{{ $team->name ?? 'Без названия' }}</strong>
                                </td>
                                <td class="text-muted">
                                    @if($team->captain ?? false)
                                        {{ $team->captain->surname ?? '' }} {{ $team->captain->name ?? '' }}
                                    @else
                                        <span class="text-danger">Нет капитана</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-muted" style="font-size: 1.2rem;">
                                        {{ $score }}
                                    </strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($rating))
                                                <i class="bi bi-star-fill text-warning me-1"></i>
                                            @elseif($i - 0.5 <= $rating)
                                                <i class="bi bi-star-half text-warning me-1"></i>
                                            @else
                                                <i class="bi bi-star text-warning me-1"></i>
                                            @endif
                                        @endfor
                                        <span class="ms-2 text-muted">({{ round($rating, 1) }})</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-trophy display-6"></i>
                                    <p class="mt-2">Нет данных для рейтинга</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(!empty($teamStats) && count($teamStats) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация графиков прогресса для каждого задания
    @foreach($teamStats as $taskId => $taskData)
    @php
        $teams = $taskData['teams'] ?? collect();
    @endphp
    @if($teams->isNotEmpty())
        try {
            const teamNames{{ $taskId }} = @json($teams->pluck('name')->toArray());
            const teamScores{{ $taskId }} = @json($teams->pluck('score')->toArray());
            if (teamNames{{ $taskId }}.length > 0 && teamScores{{ $taskId }}.length > 0) {
                initProgressChart({{ $taskId }}, teamNames{{ $taskId }}, teamScores{{ $taskId }});
            }
        } catch (e) {
            console.error('Ошибка инициализации графика для задания {{ $taskId }}:', e);
        }
    @endif
    @endforeach
    
    // Автообновление статистики каждую минуту (опционально)
    // setInterval(() => {
    //     location.reload();
    // }, 60000);
});

function initProgressChart(taskId, teamNames, teamScores) {
    const ctx = document.getElementById(`progressChart${taskId}`);
    if (!ctx) return;
    
    const canvasCtx = ctx.getContext('2d');
    
    // Создаем градиент
    const gradient = canvasCtx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(54, 162, 235, 0.6)');
    gradient.addColorStop(1, 'rgba(54, 162, 235, 0.1)');
    
    try {
        new Chart(canvasCtx, {
            type: 'bar',
            data: {
                labels: teamNames,
                datasets: [{
                    label: 'Баллы',
                    data: teamScores,
                    backgroundColor: gradient,
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1,
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    } catch (e) {
        console.error('Ошибка создания графика:', e);
    }
}
</script>
@endif
@endsection
