<?php $__env->startSection('title', 'Аналитика системы'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .metric-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    }
    .metric-value {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .metric-icon {
        font-size: 2rem;
        opacity: 0.7;
    }
    .progress {
        height: 10px;
        border-radius: 5px;
    }
    .chart-container {
        height: 300px;
        position: relative;
    }
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-ok { background-color: var(--success-color); }
    .status-warning { background-color: var(--warning-color); }
    .status-danger { background-color: var(--danger-color); }
    .metric-value-small {
        font-size: 1.8rem;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <h2 class="mb-4"><i class="bi bi-graph-up me-2"></i>Аналитика системы</h2>

    <!-- Основные метрики -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card metric-card border-start border-5 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">CPU</h6>
                            <h3 class="metric-value text-primary">
                                <?php echo e(isset($latestMetrics) ? number_format($latestMetrics->cpu_usage, 1) : '0.0'); ?>%
                            </h3>
                        </div>
                        <div class="metric-icon text-primary">
                            <i class="bi bi-cpu"></i>
                        </div>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: <?php echo e(min($latestMetrics->cpu_usage ?? 0, 100)); ?>%">
                        </div>
                    </div>
                    <small class="text-black">
                        <?php
                            $cpuValue = $latestMetrics->cpu_usage ?? 0;
                            $cpuStatus = $cpuValue > 80 ? 'danger' : ($cpuValue > 60 ? 'warning' : 'ok');
                            $cpuText = $cpuValue > 80 ? 'Критическая' : ($cpuValue > 60 ? 'Высокая' : ($cpuValue > 30 ? 'Средняя' : 'Низкая'));
                        ?>
                        <span class="status-indicator status-<?php echo e($cpuStatus); ?>"></span>
                        <?php echo e($cpuText); ?> нагрузка
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card metric-card border-start border-5 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Память</h6>
                            <h3 class="metric-value text-success">
                                <?php echo e(isset($latestMetrics) ? number_format($latestMetrics->memory_usage, 1) : '0.0'); ?>%
                            </h3>
                        </div>
                        <div class="metric-icon text-success">
                            <i class="bi bi-memory"></i>
                        </div>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo e(min($latestMetrics->memory_usage ?? 0, 100)); ?>%">
                        </div>
                    </div>
                    <small class="text-black">
                        <?php
                            $memoryValue = $latestMetrics->memory_usage ?? 0;
                            $memoryStatus = $memoryValue > 85 ? 'danger' : ($memoryValue > 70 ? 'warning' : 'ok');
                            $memoryText = $memoryValue > 85 ? 'Критично' : ($memoryValue > 70 ? 'Высокое' : ($memoryValue > 50 ? 'Среднее' : 'Низкое'));
                        ?>
                        <span class="status-indicator status-<?php echo e($memoryStatus); ?>"></span>
                        <?php echo e($memoryText); ?> использование
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card metric-card border-start border-5 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Диск</h6>
                            <h3 class="metric-value text-warning">
                                <?php echo e(isset($latestMetrics) ? number_format($latestMetrics->disk_usage, 1) : '0.0'); ?>%
                            </h3>
                        </div>
                        <div class="metric-icon text-warning">
                            <i class="bi bi-hdd"></i>
                        </div>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: <?php echo e(min($latestMetrics->disk_usage ?? 0, 100)); ?>%">
                        </div>
                    </div>
                    <small class="text-black">
                        <?php
                            $diskValue = $latestMetrics->disk_usage ?? 0;
                            $diskStatus = $diskValue > 90 ? 'danger' : ($diskValue > 80 ? 'warning' : 'ok');
                            $diskText = $diskValue > 90 ? 'Критично' : ($diskValue > 80 ? 'Мало' : ($diskValue > 60 ? 'Достаточно' : 'Много'));
                        ?>
                        <span class="status-indicator status-<?php echo e($diskStatus); ?>"></span>
                        <?php echo e($diskText); ?> свободно
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card metric-card border-start border-5 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black mb-2">Подключения</h6>
                            <h3 class="metric-value text-info metric-value-small"><?php echo e($latestMetrics->active_connections ?? 0); ?></h3>
                        </div>
                        <div class="metric-icon text-info">
                            <i class="bi bi-plug"></i>
                        </div>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: <?php echo e(min(($latestMetrics->active_connections ?? 0) / 2, 100)); ?>%">
                        </div>
                    </div>
                    <small class="text-black">
                        <?php
                            $connectionsValue = $latestMetrics->active_connections ?? 0;
                            $connectionsStatus = $connectionsValue > 100 ? 'danger' : ($connectionsValue > 50 ? 'warning' : 'ok');
                        ?>
                        <span class="status-indicator status-<?php echo e($connectionsStatus); ?>"></span>
                        Активные подключения к БД
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Графики и статистика -->
    <div class="row">
        <!-- Активность пользователей -->
        <div class="col-lg-6 mb-4">
            <div class="card task-card">
                <div class="task-card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-activity me-2"></i>Активность пользователей
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="p-3 bg-brown bg-opacity-10 rounded">
                                <h2 class="text-white"><?php echo e($activeUsers ?? 0); ?></h2>
                                <p class="text-white mb-0 small">Активных пользователей</p>
                                <small class="text-white">(последние 15 минут)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-brown bg-opacity-10 rounded">
                                <h2 class="text-white"><?php echo e($activeTeams ?? 0); ?></h2>
                                <p class="text-white mb-0 small">Активных команд</p>
                                <small class="text-white">(выполняют задания)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="text-white">Статистика по ролям:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Роль</th>
                                        <th>Количество</th>
                                        <th>Процент</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $totalUsers = \App\Models\User::count();
                                    ?>
                                    <tr>
                                        <td><span class="badge bg-danger">Администраторы</span></td>
                                        <td><?php echo e(\App\Models\User::where('role', 'admin')->count()); ?></td>
                                        <td><?php echo e($totalUsers > 0 ? round(\App\Models\User::where('role', 'admin')->count() / $totalUsers * 100, 1) : 0); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">Капитаны</span></td>
                                        <td><?php echo e(\App\Models\User::where('role', 'captain')->count()); ?></td>
                                        <td><?php echo e($totalUsers > 0 ? round(\App\Models\User::where('role', 'captain')->count() / $totalUsers * 100, 1) : 0); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-secondary">Участники</span></td>
                                        <td><?php echo e(\App\Models\User::where('role', 'participant')->count()); ?></td>
                                        <td><?php echo e($totalUsers > 0 ? round(\App\Models\User::where('role', 'participant')->count() / $totalUsers * 100, 1) : 0); ?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- История метрик -->
        <div class="col-lg-6 mb-4">
            <div class="card task-card">
                <div class="task-card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-graph-up-arrow me-2"></i>История метрик (24 часа)
                    </h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="metricsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Детальная информация -->
    <div class="row">
        <div class="col-12">
            <div class="card task-card">
                <div class="task-card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Детальная информация
                    </h4>
                    <!--<div>
                        <button id="refreshMetrics" class="btn btn-sm btn-ctf">
                            <i class="bi bi-arrow-clockwise me-1"></i>Обновить
                        </button>
                        <small class="text-muted ms-2">
                            Обновлено: <?php echo e($latestMetrics->created_at->format('H:i:s') ?? 'Никогда'); ?>

                        </small>
                    </div>-->
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-white">Информация о системе:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-server me-2 text-white"></i>
                                    <strong>ОС:</strong> <?php echo e(php_uname('s')); ?> <?php echo e(php_uname('r')); ?>

                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-code-slash me-2 text-white"></i>
                                    <strong>PHP:</strong> <?php echo e(phpversion()); ?>

                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-database me-2 text-white"></i>
                                    <strong>Laravel:</strong> <?php echo e(app()->version()); ?>

                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-hdd me-2 text-white"></i>
                                    <strong>Свободно на диске:</strong> 
                                    <?php echo e(round(disk_free_space('/') / 1024 / 1024 / 1024, 2)); ?> GB
                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-clock me-2 text-muted"></i>
                                    <strong>Время работы:</strong> 
                                    <?php
                                        $uptime = 0;
                                        if (file_exists('/proc/uptime')) {
                                            $uptime = (int)file_get_contents('/proc/uptime');
                                            $uptime = round($uptime / 3600, 1);
                                        }
                                    ?>
                                    <?php echo e($uptime); ?> часов
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-white">Состояние сервисов:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-check-circle me-2 text-muted"></i>
                                    <strong>Веб-сервер:</strong> <span class="text-success">Работает</span>
                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-check-circle me-2 text-muted"></i>
                                    <strong>База данных:</strong> <span class="text-success">Подключена</span>
                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-check-circle me-2 text-muted"></i>
                                    <strong>Очереди:</strong> 
                                    <?php if(config('queue.default') !== 'sync'): ?>
                                        <span class="text-success">Активны</span>
                                    <?php else: ?>
                                        <span class="text-warning">Синхронный режим</span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-check-circle me-2 text-muted"></i>
                                    <strong>Кэш:</strong> 
                                    <?php if(config('cache.default') !== 'array'): ?>
                                        <span class="text-success">Работает</span>
                                    <?php else: ?>
                                        <span class="text-warning">Массив</span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2 text-muted">
                                    <i class="bi bi-check-circle me-2 text-muted"></i>
                                    <strong>Почта:</strong> 
                                    <?php if(config('mail.default') !== 'log'): ?>
                                        <span class="text-success">Настроен</span>
                                    <?php else: ?>
                                        <span class="text-warning">Тестовый режим</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация графика
    let metricsChart = null;
    
    try {
        metricsChart = initMetricsChart();
    } catch (e) {
        console.error('Ошибка инициализации графика:', e);
    }
    
    // Кнопка обновления метрик
    const refreshBtn = document.getElementById('refreshMetrics');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const button = this;
            const originalHtml = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Обновление...';
            
            fetch('<?php echo e(route("admin.analytics.metrics")); ?>')
                .then(response => response.json())
                .then(data => {
                    // Обновляем метрики на странице
                    updateMetrics(data);
                    
                    // Обновляем время
                    const timeElement = document.querySelector('.text-muted.ms-2');
                    if (timeElement) {
                        timeElement.textContent = 'Обновлено: ' + data.updated_at;
                    }
                    
                    // Восстанавливаем кнопку
                    setTimeout(() => {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    }, 1000);
                })
                .catch(error => {
                    console.error('Ошибка обновления метрик:', error);
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                    
                    alert('Ошибка обновления метрик: ' + error.message);
                });
        });
    }
    
    // Автообновление метрик каждые 30 секунд
    setInterval(() => {
        fetch('<?php echo e(route("admin.analytics.metrics")); ?>')
            .then(response => response.json())
            .then(data => updateMetrics(data))
            .catch(error => console.error('Ошибка автообновления:', error));
    }, 30000);
});

function initMetricsChart() {
    const canvas = document.getElementById('metricsChart');
    if (!canvas) return null;
    
    const ctx = canvas.getContext('2d');
    
    // Подготовка данных из PHP
    const history = <?php echo json_encode($metricsHistory ?? [], 15, 512) ?>;
    const labels = Object.keys(history);
    const cpuData = labels.map(label => parseFloat(history[label]?.cpu_usage || 0).toFixed(1));
    const memoryData = labels.map(label => parseFloat(history[label]?.memory_usage || 0).toFixed(1));
    const diskData = labels.map(label => parseFloat(history[label]?.disk_usage || 0).toFixed(1));
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'CPU %',
                    data: cpuData,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Память %',
                    data: memoryData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Диск %',
                    data: diskData,
                    borderColor: 'rgb(255, 159, 64)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#fff'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#fff',
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#fff',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

function updateMetrics(data) {
    // Обновляем значения метрик с ограничением до 100%
    const cpuValue = Math.min(parseFloat(data.cpu_usage) || 0, 100);
    const memoryValue = Math.min(parseFloat(data.memory_usage) || 0, 100);
    const diskValue = Math.min(parseFloat(data.disk_usage) || 0, 100);
    
    // Обновляем CPU
    const cpuElement = document.querySelector('.text-primary.metric-value');
    if (cpuElement) cpuElement.textContent = cpuValue.toFixed(1) + '%';
    document.querySelector('.progress-bar.bg-primary').style.width = cpuValue + '%';
    
    // Обновляем Memory
    const memoryElement = document.querySelector('.text-success.metric-value');
    if (memoryElement) memoryElement.textContent = memoryValue.toFixed(1) + '%';
    document.querySelector('.progress-bar.bg-success').style.width = memoryValue + '%';
    
    // Обновляем Disk
    const diskElement = document.querySelector('.text-warning.metric-value');
    if (diskElement) diskElement.textContent = diskValue.toFixed(1) + '%';
    document.querySelector('.progress-bar.bg-warning').style.width = diskValue + '%';
    
    // Обновляем Connections
    const connectionsElement = document.querySelector('.text-info.metric-value');
    if (connectionsElement) connectionsElement.textContent = data.active_connections;
    document.querySelector('.progress-bar.bg-info').style.width = Math.min(data.active_connections / 2, 100) + '%';
    
    // Обновляем статусы
    updateStatusIndicators(cpuValue, memoryValue, diskValue, data.active_connections);
}

function updateStatusIndicators(cpu, memory, disk, connections) {
    // CPU статус
    const cpuStatusEl = document.querySelector('.status-indicator:has(+ small:contains("нагрузка"))');
    const cpuTextEl = cpuStatusEl?.nextElementSibling;
    if (cpuStatusEl && cpuTextEl) {
        let status = 'ok';
        let text = 'Низкая';
        if (cpu > 80) {
            status = 'danger';
            text = 'Критическая';
        } else if (cpu > 60) {
            status = 'warning';
            text = 'Высокая';
        } else if (cpu > 30) {
            status = 'ok';
            text = 'Средняя';
        }
        cpuStatusEl.className = `status-indicator status-${status}`;
        cpuTextEl.textContent = ` ${text} нагрузка`;
    }
    
    // Memory статус
    const memoryStatusEl = document.querySelector('.status-indicator:has(+ small:contains("использование"))');
    const memoryTextEl = memoryStatusEl?.nextElementSibling;
    if (memoryStatusEl && memoryTextEl) {
        let status = 'ok';
        let text = 'Низкое';
        if (memory > 85) {
            status = 'danger';
            text = 'Критично';
        } else if (memory > 70) {
            status = 'warning';
            text = 'Высокое';
        } else if (memory > 50) {
            status = 'ok';
            text = 'Среднее';
        }
        memoryStatusEl.className = `status-indicator status-${status}`;
        memoryTextEl.textContent = ` ${text} использование`;
    }
    
    // Disk статус
    const diskStatusEl = document.querySelector('.status-indicator:has(+ small:contains("свободно"))');
    const diskTextEl = diskStatusEl?.nextElementSibling;
    if (diskStatusEl && diskTextEl) {
        let status = 'ok';
        let text = 'Много';
        if (disk > 90) {
            status = 'danger';
            text = 'Критично';
        } else if (disk > 80) {
            status = 'warning';
            text = 'Мало';
        } else if (disk > 60) {
            status = 'ok';
            text = 'Достаточно';
        }
        diskStatusEl.className = `status-indicator status-${status}`;
        diskTextEl.textContent = ` ${text} свободно`;
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/ctf-platform/resources/views/admin/analytics.blade.php ENDPATH**/ ?>