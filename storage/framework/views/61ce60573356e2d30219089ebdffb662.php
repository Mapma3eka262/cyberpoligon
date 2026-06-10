<?php $__env->startSection('title', 'Статистика'); ?>

<?php $__env->startSection('styles'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart me-2"></i>Статистика</h2>
        <div>
            <a href="<?php echo e(route('admin.stats.export') ?? '#'); ?>" class="btn btn-ctf-success">
                <i class="bi bi-download me-2"></i>Экспорт CSV
            </a>
        </div>
    </div>

    <!-- Общая статистика -->
    <?php
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
    ?>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-start border-5 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Всего команд</h6>
                            <h3 class="text-primary"><?php echo e($totalStats['total_teams']); ?></h3>
                        </div>
                        <div class="text-primary" style="font-size: 2rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-black">
                            <i class="bi bi-activity me-1"></i>
                            Активных: <?php echo e($totalStats['active_teams']); ?>

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
                            <h3 class="text-success"><?php echo e($totalStats['total_tasks']); ?></h3>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="bi bi-flag-fill"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-black">
                            <i class="bi bi-check-circle me-1"></i>
                            Активных: <?php echo e($totalStats['active_tasks']); ?>

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
                            <h3 class="text-warning"><?php echo e($totalStats['total_flag_attempts']); ?></h3>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;">
                            <i class="bi bi-send-fill"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-black">
                            <i class="bi bi-check-circle me-1"></i>
                            Верных: <?php echo e($totalStats['correct_attempts']); ?>

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
                            <h3 class="text-info"><?php echo e($totalStats['success_rate']); ?>%</h3>
                        </div>
                        <div class="text-info" style="font-size: 2rem;">
                            <i class="bi bi-percent"></i>
                        </div>
                    </div>
                    <div class="progress progress-thin mt-2">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: <?php echo e(min($totalStats['success_rate'], 100)); ?>%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика по заданиям -->
    <?php if(!empty($teamStats) && count($teamStats) > 0): ?>
        <?php $__currentLoopData = $teamStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taskId => $taskData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $task = $taskData['task'] ?? null;
            $teams = $taskData['teams'] ?? collect();
        ?>
        
        <?php if($task): ?>
        <div class="card task-card mb-4">
            <div class="task-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?php echo e($task->name ?? 'Неизвестное задание'); ?></h4>
                    <?php if($task): ?>
                    <small class="text-muted">
                        <?php echo e($task->target_ip ?? 'N/A'); ?> • 
                        <?php echo e($task->time_limit_minutes ?? 0); ?> минут • 
                        <?php echo e($teams->count()); ?> команд
                    </small>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if(isset($task->is_active) && $task->is_active): ?>
                        <span class="badge bg-success">Активно</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Завершено</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if($teams->isEmpty()): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Нет команд, выполняющих это задание</p>
                    </div>
                <?php else: ?>
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
                                <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $team = (array) $team;
                                    $flag1 = $team['flag1_found'] ?? false;
                                    $flag2 = $team['flag2_found'] ?? false;
                                    $progress = $team['progress_percent'] ?? 0;
                                    $wrongAttempts = $team['wrong_attempts'] ?? 0;
                                    $score = $team['score'] ?? 0;
                                    $remainingTime = $team['remaining_time'] ?? '00:00';
                                    $status = $team['status'] ?? 'not_started';
                                ?>
                                <tr>
                                    <td>
                                        <div class="text-center">
                                            <?php if($index == 0): ?>
                                                <span class="badge bg-warning" style="font-size: 1.2rem;">🥇</span>
                                            <?php elseif($index == 1): ?>
                                                <span class="badge bg-secondary" style="font-size: 1.2rem;">🥈</span>
                                            <?php elseif($index == 2): ?>
                                                <span class="badge bg-danger" style="font-size: 1.2rem;">🥉</span>
                                            <?php else: ?>
                                                <strong>#<?php echo e($index + 1); ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <strong><?php echo e($team['name'] ?? 'Без названия'); ?></strong>
                                    </td>
                                    <td>
                                        <div class="team-progress mb-2">
                                            <div class="flag-progress flag-1-progress" 
                                                 style="width: <?php echo e($flag1 ? 50 : 0); ?>%"></div>
                                            <div class="flag-progress flag-2-progress" 
                                                 style="width: <?php echo e($flag2 ? 50 : 0); ?>%; 
                                                        left: <?php echo e($flag1 ? 50 : 0); ?>%"></div>
                                            <div class="team-name">
                                                <?php echo e($progress); ?>%
                                            </div>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo e($progress); ?>%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <?php if($flag1): ?>
                                                    <span class="badge bg-success">✅ Флаг 1</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">❌ Флаг 1</span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if($flag2): ?>
                                                    <span class="badge bg-success">✅ Флаг 2</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">❌ Флаг 2</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($wrongAttempts > 5 ? 'danger' : ($wrongAttempts > 0 ? 'warning' : 'success')); ?>">
                                            <?php echo e($wrongAttempts); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary"><?php echo e($score); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($remainingTime == '00:00' ? 'danger' : 'info'); ?>">
                                            <?php echo e($remainingTime); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php switch($status):
                                            case ('completed'): ?>
                                                <span class="badge bg-success">✅ Завершено</span>
                                                <?php break; ?>
                                            <?php case ('in_progress'): ?>
                                                <span class="badge bg-warning">⏳ В процессе</span>
                                                <?php break; ?>
                                            <?php case ('timeout'): ?>
                                                <span class="badge bg-danger">⏰ Время вышло</span>
                                                <?php break; ?>
                                            <?php default: ?>
                                                <span class="badge bg-secondary">❌ Не начато</span>
                                        <?php endswitch; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="card task-card mb-4">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-bar-chart display-1"></i>
                <h4 class="mt-3">Нет данных для статистики</h4>
                <p>Создайте задания и начните соревнование, чтобы увидеть статистику</p>
            </div>
        </div>
    <?php endif; ?>

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
                        <?php
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
                        ?>
                        
                        <?php if(count($teamsStats) > 0): ?>
                            <?php $__currentLoopData = $teamsStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $teamStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $team = $teamStat['team'];
                                $score = $teamStat['total_score'];
                                $maxScore = !empty($teamsStats) ? max(array_column($teamsStats, 'total_score')) : 1;
                                $rating = $maxScore > 0 ? ($score / $maxScore) * 5 : 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="text-center">
                                        <?php if($index == 0): ?>
                                            <span class="badge bg-warning" style="font-size: 1.5rem; padding: 8px 12px;">🥇</span>
                                        <?php elseif($index == 1): ?>
                                            <span class="badge bg-secondary" style="font-size: 1.5rem; padding: 8px 12px;">🥈</span>
                                        <?php elseif($index == 2): ?>
                                            <span class="badge bg-danger" style="font-size: 1.5rem; padding: 8px 12px;">🥉</span>
                                        <?php else: ?>
                                            <strong style="font-size: 1.2rem;">#<?php echo e($index + 1); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    <strong><?php echo e($team->name ?? 'Без названия'); ?></strong>
                                </td>
                                <td class="text-muted">
                                    <?php if($team->captain ?? false): ?>
                                        <?php echo e($team->captain->surname ?? ''); ?> <?php echo e($team->captain->name ?? ''); ?>

                                    <?php else: ?>
                                        <span class="text-danger">Нет капитана</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-muted" style="font-size: 1.2rem;">
                                        <?php echo e($score); ?>

                                    </strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= floor($rating)): ?>
                                                <i class="bi bi-star-fill text-warning me-1"></i>
                                            <?php elseif($i - 0.5 <= $rating): ?>
                                                <i class="bi bi-star-half text-warning me-1"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star text-warning me-1"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span class="ms-2 text-muted">(<?php echo e(round($rating, 1)); ?>)</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-trophy display-6"></i>
                                    <p class="mt-2">Нет данных для рейтинга</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php if(!empty($teamStats) && count($teamStats) > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация графиков прогресса для каждого задания
    <?php $__currentLoopData = $teamStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taskId => $taskData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $teams = $taskData['teams'] ?? collect();
    ?>
    <?php if($teams->isNotEmpty()): ?>
        try {
            const teamNames<?php echo e($taskId); ?> = <?php echo json_encode($teams->pluck('name')->toArray(), 15, 512) ?>;
            const teamScores<?php echo e($taskId); ?> = <?php echo json_encode($teams->pluck('score')->toArray(), 15, 512) ?>;
            if (teamNames<?php echo e($taskId); ?>.length > 0 && teamScores<?php echo e($taskId); ?>.length > 0) {
                initProgressChart(<?php echo e($taskId); ?>, teamNames<?php echo e($taskId); ?>, teamScores<?php echo e($taskId); ?>);
            }
        } catch (e) {
            console.error('Ошибка инициализации графика для задания <?php echo e($taskId); ?>:', e);
        }
    <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    
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
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/ctf-platform/resources/views/admin/stats.blade.php ENDPATH**/ ?>