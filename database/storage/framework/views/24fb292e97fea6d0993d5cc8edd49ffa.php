<?php $__env->startPush('head'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('title', 'Арена'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .flag-status {
        font-size: 1.2rem;
        font-weight: bold;
    }
    .flag-status.found {
        color: var(--success-color);
    }
    .flag-status.not-found {
        color: var(--danger-color);
    }
    .attempt-history {
        max-height: 300px;
        overflow-y: auto;
    }
    .target-machine {
        font-family: 'Courier New', monospace;
        background: var(--dark-bg);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8">
            <!-- Информация о команде -->
            <div class="card task-card mb-4">
                <div class="task-card-header">
                    <h3 class="mb-0">
                        <i class="bi bi-flag-fill me-2"></i>Арена CTF
                        <?php if($team): ?>
                        <span class="float-end badge bg-primary"><?php echo e($team->name); ?></span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="task-card-body">
                    <?php if(!$activeTask): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-clock-history display-1 text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">Нет активных заданий</h4>
                            <p class="text-muted">Ожидайте, пока администратор назначит задание вашей команде.</p>
                            <div class="mt-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Ожидание...</span>
                                </div>
                                <p class="mt-2 small text-muted">Автообновление каждые 30 секунд</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Информация о задании -->
                        <div class="mb-4">
                            <h4><?php echo e($activeTask->name ?? 'Название задания'); ?></h4>
                            <?php if(!empty($activeTask->description)): ?>
                                <p class="text-muted"><?php echo e($activeTask->description); ?></p>
                            <?php endif; ?>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>Время на выполнение:</strong> <?php echo e($activeTask->time_limit_minutes ?? 0); ?> минут</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Начало:</strong> 
                                        <?php if(!empty($activeTask->pivot) && !empty($activeTask->pivot->start_time)): ?>
                                            <?php echo e(date('H:i', strtotime($activeTask->pivot->start_time))); ?>

                                        <?php else: ?>
                                            Не указано
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Машина для атаки -->
			<div class="target-machine mb-4">
			    <div class="d-flex justify-content-between align-items-center">
			        <div>
			            <i class="bi bi-pc-display me-2"></i>
			            <strong>Целевая машина:</strong>
			        </div>
			        <code class="fs-5"><?php echo e($activeTask->target_ip ?? 'Не указан'); ?></code>
			    </div>
			    
			    <?php if(!empty($activeTask->subnet_mask)): ?>
			    <div class="mt-2">
			        <i class="bi bi-diagram-3 me-2"></i>
			        <strong>Подсеть:</strong> <?php echo e($activeTask->subnet_mask); ?>

			    </div>
			    <?php endif; ?>
			    
			    <!-- ДОСТУП К ВИРТУАЛЬНОЙ МАШИНЕ (Proxmox) -->
			    <?php if(!empty($activeTask->vm_id)): ?>
			    <div class="mt-3 pt-3 border-top">
			        <div class="d-flex justify-content-between align-items-center">
			            <div>
			                <p class="mb-1">
			                    <i class="bi bi-display me-2"></i>
			                    <strong>VM ID:</strong> <?php echo e($activeTask->vm_id); ?>

			                    <?php if(!empty($activeTask->proxmox_node)): ?>
			                        <span class="text-muted">(нода: <?php echo e($activeTask->proxmox_node); ?>)</span>
			                    <?php endif; ?>
			                </p>
			                <p class="mb-0 small text-muted">
			                    Для графического доступа к виртуальной машине
			                </p>
			            </div>
			            <div>
			                <button type="button" class="btn btn-sm btn-ctf-primary open-vm-btn" 
			                        data-task-id="<?php echo e($activeTask->id); ?>">
			                    <i class="bi bi-display me-2"></i><p class="text-muted">Открыть ВМ</p>
			                </button>
			            </div>
			        </div>
			    </div>
			    <?php endif; ?>
			</div>

                        <!-- Таймер -->
                        <div class="countdown-timer mb-4">
                            <div class="timer-label mb-2">Оставшееся время:</div>
                            <div id="task-countdown" 
                                 data-countdown="<?php echo e(!empty($activeTask->pivot) && !empty($activeTask->pivot->end_time) ? $activeTask->pivot->end_time : ''); ?>">
                                <?php if(empty($timeRemaining) || (isset($timeRemaining['is_expired']) && $timeRemaining['is_expired'])): ?>
                                    <span class="text-danger">ВРЕМЯ ВЫШЛО!</span>
                                <?php else: ?>
                                    <span id="task-hours"><?php echo e(isset($timeRemaining['hours']) ? str_pad($timeRemaining['hours'], 2, '0', STR_PAD_LEFT) : '00'); ?></span>:
                                    <span id="task-minutes"><?php echo e(isset($timeRemaining['minutes']) ? str_pad($timeRemaining['minutes'], 2, '0', STR_PAD_LEFT) : '00'); ?></span>:
                                    <span id="task-seconds"><?php echo e(isset($timeRemaining['seconds']) ? str_pad($timeRemaining['seconds'], 2, '0', STR_PAD_LEFT) : '00'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Прогресс по флагам -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Флаг 1</h5>
                                        <div class="flag-status mb-2 <?php echo e((!empty($activeTask->pivot) && $activeTask->pivot->flag1_found) ? 'found' : 'not-found'); ?>">
                                            <?php if(!empty($activeTask->pivot) && $activeTask->pivot->flag1_found): ?>
                                                ✅ Найден (+30 баллов)
                                            <?php else: ?>
                                                ❌ Не найден
                                            <?php endif; ?>
                                        </div>
                                        <p class="small text-muted mb-0">
                                            Первый флаг: 30 баллов
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Флаг 2</h5>
                                        <div class="flag-status mb-2 <?php echo e((!empty($activeTask->pivot) && $activeTask->pivot->flag2_found) ? 'found' : 'not-found'); ?>">
                                            <?php if(!empty($activeTask->pivot) && $activeTask->pivot->flag2_found): ?>
                                                ✅ Найден (+70 баллов)
                                            <?php else: ?>
                                                ❌ Не найден
                                            <?php endif; ?>
                                        </div>
                                        <p class="small text-muted mb-0">
                                            Второй флаг: 70 баллов
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-brown bg-opacity-10 rounded">
                                    <h2 class="text-black"><?php echo e($currentScore ?? (!empty($activeTask->pivot) ? $activeTask->pivot->score : 0)); ?></h2>
                                    <p class="mb-0 small">
                                        Баллов (без штрафов)
                                        <?php if(!empty($activeTask->pivot) && $activeTask->pivot->wrong_attempts_count > 0): ?>
                                        <br><small class="text-danger">Штраф: -<?php echo e($activeTask->pivot->wrong_attempts_count * 3); ?> баллов</small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                    <h2 class="text-black"><?php echo e(!empty($activeTask->pivot) ? $activeTask->pivot->wrong_attempts_count : 0); ?></h2>
                                    <p class="mb-0 small">Неверных попыток</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                    <h2 class="text-info"><?php echo e($flagAttempts ? $flagAttempts->count() : 0); ?></h2>
                                    <p class="mb-0 small">Всего попыток</p>
                                </div>
                            </div>
                        </div>

                        <!-- Формы ввода флагов -->
                        <?php if(empty($timeRemaining) || (isset($timeRemaining['is_expired']) && !$timeRemaining['is_expired'])): ?>
                        <div class="row">
                            <!-- Форма для флага 1 -->
                            <?php if(empty($activeTask->pivot) || !$activeTask->pivot->flag1_found): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header bg-success bg-opacity-25">
                                        <h5 class="mb-0">Флаг 1</h5>
                                    </div>
                                    <div class="card-body">
                                        <form class="flag-form" data-flag-number="1">
                                            <?php echo csrf_field(); ?>
                                            <div class="mb-3">
                                                <label for="flag1" class="form-label">Введите первый флаг:</label>
                                                <input type="text" class="form-control form-control-ctf flag-input" 
                                                       id="flag1" name="flag_value" 
                                                       placeholder="CTF{...}" required>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-ctf-success">
                                                    <i class="bi bi-send me-2"></i>Отправить флаг 1
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Форма для флага 2 -->
                            <?php if(empty($activeTask->pivot) || !$activeTask->pivot->flag2_found): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header bg-success bg-opacity-25">
                                        <h5 class="mb-0">Флаг 2</h5>
                                    </div>
                                    <div class="card-body">
                                        <form class="flag-form" data-flag-number="2">
                                            <?php echo csrf_field(); ?>
                                            <div class="mb-3">
                                                <label for="flag2" class="form-label">Введите второй флаг:</label>
                                                <input type="text" class="form-control form-control-ctf flag-input" 
                                                       id="flag2" name="flag_value" 
                                                       placeholder="CTF{...}" required>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-ctf-success">
                                                    <i class="bi bi-send me-2"></i>Отправить флаг 2
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if(!empty($activeTask->pivot) && $activeTask->pivot->flag1_found && $activeTask->pivot->flag2_found): ?>
                            <div class="col-12">
                                <div class="alert alert-success text-center">
                                    <h4><i class="bi bi-trophy me-2"></i>Все флаги найдены!</h4>
                                    <p class="mb-0">Поздравляем! Вы успешно выполнили задание.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-clock-history me-2"></i>
                            Время выполнения задания истекло. Новые флаги приниматься не будут.
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- История попыток -->
            <div class="card task-card mb-4">
                <div class="task-card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>История попыток
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($flagAttempts) || $flagAttempts->isEmpty()): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-6"></i>
                            <p class="mt-2">Нет отправленных флагов</p>
                        </div>
                    <?php else: ?>
                        <div class="attempt-history p-3">
                            <?php $__currentLoopData = $flagAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="attempt-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="small text-muted">Флаг <?php echo e($attempt->flag_number ?? 1); ?></strong>
                                        <div class="small text-muted">
                                            <?php echo e($attempt->created_at->format('H:i:s') ?? now()->format('H:i:s')); ?>

                                        </div>
                                    </div>
                                    <div>
                                        <?php if($attempt->is_correct): ?>
                                            <span class="badge bg-success">✅ Верно</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">❌ Неверно</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <code class="small"><?php echo e(Str::limit($attempt->attempt_value ?? '', 50)); ?></code>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Правила -->
            <div class="card task-card">
                <div class="task-card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Правила арены
                    </h4>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <p class="bi bi-check-circle text-success me-2">
                            Флаг имеет формат <code>CTF{...}</code></p>
                        </li>
                        <li class="mb-2">
                            <p class="bi bi-check-circle text-success me-2">
                            1-й флаг = 30 баллов, 2-й флаг = 70 баллов</p>
                        </li>
                        <li class="mb-2">
                            <p class="bi bi-x-circle text-danger me-2">
                            За неверную попытку: -3 балла</p>
                        </li>
                        <li class="mb-2">
                            <p class="bi bi-clock text-warning me-2">
                            Время ограничено (смотри таймер)</p>
                        </li>
                        <li>
                            <p class="bi bi-shield-check text-primary me-2">
                            Не атакуйте инфраструктуру платформы</p>
                        </li>
                        <li>
                            <p class="bi bi-shield-check text-danger me-2">
                            За дополнительной информацией можно обратиться
                            <a href="/guide" target="_blank">сюда</a>
                            </p>
                        </li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Совет:</strong> Проверяйте флаги на правильный формат перед отправкой.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация таймера задания
    const taskCountdown = document.getElementById('task-countdown');
    if (taskCountdown && taskCountdown.dataset.countdown) {
        if (typeof CTFPlatform !== 'undefined' && typeof CTFPlatform.initCountdownTimers === 'function') {
            CTFPlatform.initCountdownTimers();
        }
    }

    // Получаем CSRF токен
    function getCsrfToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken && metaToken.content) {
            return metaToken.content;
        }
        return '<?php echo e(csrf_token()); ?>';
    }

    // Обработка отправки флагов
    const flagForms = document.querySelectorAll('.flag-form');
    flagForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const flagNumber = this.dataset.flagNumber;
            const flagValue = this.querySelector('input[name="flag_value"]').value.trim();
            const submitButton = this.querySelector('button[type="submit"]');
            
            if (!flagValue) {
                alert('Введите флаг!');
                return;
            }
            
            // Блокируем кнопку на время отправки
            submitButton.disabled = true;
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Отправка...';
            
            // Получаем CSRF токен
            const csrfToken = getCsrfToken();
            
            // Отправляем AJAX запрос
            fetch('<?php echo e(route("arena.submit-flag")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    flag_number: flagNumber,
                    flag_value: flagValue
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Показываем уведомление
                    if (typeof CTFPlatform !== 'undefined' && typeof CTFPlatform.showNotification === 'function') {
                        CTFPlatform.showNotification(data.message, 'success');
                    } else {
                        alert('✅ ' + data.message);
                    }
                    
                    // Скрываем форму найденного флага
                    const formCard = this.closest('.col-md-6');
                    if (formCard) {
                        formCard.style.display = 'none';
                    }
                    
                    // Обновляем статус флага
                    const flagStatusElements = document.querySelectorAll('.flag-status');
                    if (flagStatusElements.length >= flagNumber) {
                        const flagStatus = flagStatusElements[flagNumber - 1];
                        flagStatus.innerHTML = '✅ Найден (+' + (flagNumber == 1 ? '30' : '70') + ' баллов)';
                        flagStatus.classList.add('found');
                        flagStatus.classList.remove('not-found');
                    }
                    
                    // Обновляем счет (блок с классом bg-brown)
                    const scoreElements = document.querySelectorAll('.bg-brown.bg-opacity-10 h2');
                    if (scoreElements.length > 0 && data.score !== undefined) {
                        scoreElements[0].textContent = data.score;
                    }
                    
                    // Обновляем счетчик неправильных попыток (блок с классом bg-warning)
                    const wrongAttemptElements = document.querySelectorAll('.bg-warning.bg-opacity-10 h2');
                    if (wrongAttemptElements.length > 0 && data.wrong_attempts !== undefined) {
                        wrongAttemptElements[0].textContent = data.wrong_attempts;
                    }
                    
                    // Обновляем страницу через 2 секунды
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                } else {
                    // Показываем ошибку
                    if (typeof CTFPlatform !== 'undefined' && typeof CTFPlatform.showNotification === 'function') {
                        CTFPlatform.showNotification(data.message, 'error');
                    } else {
                        alert('❌ ' + data.message);
                    }
                    
                    // Обновляем счет
                    const scoreElements = document.querySelectorAll('.bg-brown.bg-opacity-10 h2');
                    if (scoreElements.length > 0 && data.score !== undefined) {
                        scoreElements[0].textContent = data.score;
                    }
                    
                    // Обновляем счетчик неправильных попыток
                    const wrongAttemptElements = document.querySelectorAll('.bg-warning.bg-opacity-10 h2');
                    if (wrongAttemptElements.length > 0 && data.wrong_attempts !== undefined) {
                        wrongAttemptElements[0].textContent = data.wrong_attempts;
                    }
                    
                    // Восстанавливаем кнопку
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка сети: ' + error.message);
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    });

    // Автообновление страницы каждые 30 секунд (если нет активного задания)
    <?php if(!$activeTask): ?>
    setInterval(() => {
        window.location.reload();
    }, 30000);
    <?php endif; ?>

    // Автообновление таймера каждую секунду
    const timerInterval = setInterval(() => {
        const countdownElement = document.getElementById('task-countdown');
        if (countdownElement && countdownElement.dataset.countdown) {
            const endTime = new Date(countdownElement.dataset.countdown).getTime();
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                countdownElement.innerHTML = '<span class="text-danger">ВРЕМЯ ВЫШЛО!</span>';
                
                // Блокируем формы
                flagForms.forEach(form => {
                    const button = form.querySelector('button[type="submit"]');
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = 'Время вышло';
                    }
                });
                
                // Показываем сообщение
                if (!document.querySelector('.alert-warning')) {
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-warning mt-3';
                    alert.innerHTML = '<i class="bi bi-clock-history me-2"></i>Время выполнения задания истекло. Новые флаги приниматься не будут.';
                    const parent = countdownElement.parentElement;
                    if (parent && parent.nextElementSibling) {
                        parent.nextElementSibling.appendChild(alert);
                    }
                }
                
                // Останавливаем таймер
                clearInterval(timerInterval);
            } else {
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                const hoursElement = document.getElementById('task-hours');
                const minutesElement = document.getElementById('task-minutes');
                const secondsElement = document.getElementById('task-seconds');
                
                if (hoursElement) hoursElement.textContent = hours.toString().padStart(2, '0');
                if (minutesElement) minutesElement.textContent = minutes.toString().padStart(2, '0');
                if (secondsElement) secondsElement.textContent = seconds.toString().padStart(2, '0');
            }
        }
    }, 1000);

    const openVmBtn = document.querySelector('.open-vm-btn');
    if (openVmBtn) {
        openVmBtn.addEventListener('click', function() {
            const taskId = this.dataset.taskId;
            const btn = this;
            
            console.log('Кнопка нажата, taskId:', taskId); // Для отладки
            
            // Блокируем кнопку на время запроса
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Подключение...';
            
            // Получаем CSRF токен
            function getCsrfToken() {
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken && metaToken.content) {
                    return metaToken.content;
                }
                return '<?php echo e(csrf_token()); ?>';
            }
            
            // Отправляем запрос на получение VNC доступа
            fetch(`/tasks/${taskId}/vm-console`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Ответ от сервера:', data); // Для отладки
                
                if (data.success && data.novnc_url) {
                    // Открываем NoVNC в новом окне
                    const vmWindow = window.open(
                        data.novnc_url,
                        'vm-console-' + taskId,
                        'width=1200,height=800,scrollbars=yes,resizable=yes'
                    );
                    
                    if (!vmWindow || vmWindow.closed) {
                        alert('Пожалуйста, разрешите всплывающие окна для этого сайта');
                    }
                    
                    // Показываем уведомление
                    if (typeof CTFPlatform !== 'undefined' && typeof CTFPlatform.showNotification === 'function') {
                        CTFPlatform.showNotification('Открывается доступ к виртуальной машине...', 'info');
                    }
                } else {
                    alert('Ошибка: ' + (data.message || 'Не удалось получить доступ к VM'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка сети: ' + error.message);
            })
            .finally(() => {
                // Восстанавливаем кнопку через 2 секунды
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 2000);
            });
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/ctf-platform/resources/views/arena.blade.php ENDPATH**/ ?>