<?php $__env->startSection('title', 'Личный кабинет'); ?>

<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <div class="row">
        <!-- Основная информация -->
        <div class="col-lg-8">
            <div class="card task-card mb-4">
                <div class="task-card-header">
                    <h3 class="mb-0">Личные данные</h3>
                </div>
                <div class="task-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ФИО:</strong> <?php echo e($user->surname); ?> <?php echo e($user->name); ?> <?php echo e($user->patronymic); ?></p>
                            <p><strong>Логин:</strong> <?php echo e($user->username); ?></p>
                            <p><strong>Роль:</strong> 
                                <?php if($user->isAdmin()): ?>
                                    <span class="badge bg-danger">Администратор</span>
                                <?php elseif($user->isCaptain()): ?>
                                    <span class="badge bg-warning">Капитан</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Участник</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo e($user->email); ?></p>
                            <p><strong>Телефон:</strong> <?php echo e($user->phone ?? 'Не указан'); ?></p>
                            <p><strong>Дата регистрации:</strong> <?php echo e($user->created_at->format('d.m.Y')); ?></p>
                        </div>
                    </div>
                    
                    <?php if($user->isCaptain() && $team): ?>
                        <div class="mt-3">
                            <p><strong>Команда:</strong> <?php echo e($team->name); ?></p>
                            <p><strong>Статус капитана:</strong> ✅ Активен</p>
                        </div>
                    <?php elseif($team): ?>
                        <div class="mt-3">
                            <p><strong>Команда:</strong> <?php echo e($team->name); ?></p>
                            <p><strong>Капитан:</strong> <?php echo e($team->captain->name); ?> <?php echo e($team->captain->surname); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Вы не состоите в команде. Ожидайте приглашения от капитана.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Состав команды -->
            <?php if($team): ?>
            <div class="card task-card mb-4">
                <div class="task-card-header">
                    <h3 class="mb-0">Состав команды</h3>
                </div>
                <div class="task-card-body">
                    <div class="table-responsive">
                        <table class="table table-ctf">
                            <thead>
                                <tr>
                                    <th>ФИО</th>
                                    <th>Роль</th>
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Капитан -->
                                <tr class="table-warning">
                                    <td class="text-white">
                                        <strong><?php echo e($team->captain->surname); ?> <?php echo e($team->captain->name); ?></strong>
                                        <span class="badge bg-warning ms-2">Капитан</span>
                                    </td>
                                    <td class="text-white">Капитан</td>
                                    <td class="text-white"><?php echo e($team->captain->email); ?></td>
                                    <td class="text-white"><?php echo e($team->captain->phone ?? '—'); ?></td>
                                    <td>
                                        <span class="badge bg-success">Активен</span>
                                    </td>
                                </tr>
                                
                                <!-- Участники -->
                                <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="text-white"><?php echo e($member->surname); ?> <?php echo e($member->name); ?></td>
                                    <td class="text-white">Участник</td>
                                    <td class="text-white"><?php echo e($member->email); ?></td>
                                    <td class="text-white"><?php echo e($member->phone ?? '—'); ?></td>
                                    <td>
                                        <?php if($member->temporary_password): ?>
                                            <span class="badge bg-warning">Ожидает активации</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Активен</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if($members->isEmpty()): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Нет других участников в команде
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Форма добавления участников (только для капитанов) -->
            <?php if($user->isCaptain() && $team): ?>
            <div class="card task-card mb-4">
                <div class="task-card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Добавить участника</h3>
                    <div>
                        <button type="button" class="btn btn-outline-light btn-sm toggle-add-mode" data-mode="new">
                            Новый пользователь
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm toggle-add-mode" data-mode="existing">
                            Существующий пользователь
                        </button>
                    </div>
                </div>
                <div class="task-card-body">
                    <!-- Форма для нового пользователя -->
                    <div id="add-new-user-form">
                        <form method="POST" action="<?php echo e(route('register.participant')); ?>" id="add-member-form">
                            <?php echo csrf_field(); ?>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="participant-surname" class="form-label">Фамилия *</label>
                                    <input type="text" class="form-control form-control-ctf" 
                                           id="participant-surname" name="surname" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="participant-name" class="form-label">Имя *</label>
                                    <input type="text" class="form-control form-control-ctf" 
                                           id="participant-name" name="name" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="participant-patronymic" class="form-label">Отчество</label>
                                    <input type="text" class="form-control form-control-ctf" 
                                           id="participant-patronymic" name="patronymic">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="participant-email" class="form-label">Email *</label>
                                <input type="email" class="form-control form-control-ctf" 
                                       id="participant-email" name="email" required>
                                <div class="form-text">
                                    На этот email будет отправлено приглашение с временным паролем
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-ctf-success">
                                    <i class="bi bi-person-plus me-2"></i>Создать и добавить
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Форма для существующего пользователя -->
                    <div id="add-existing-user-form" style="display: none;">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Выберите пользователя из списка зарегистрированных, у которых нет команды.
                        </div>

                        <?php if($availableUsers->count() > 0): ?>
                            <form method="POST" action="<?php echo e(route('add.existing.participant')); ?>" id="add-existing-member-form">
                                <?php echo csrf_field(); ?>
                                
                                <div class="mb-3">
                                    <label for="existing-user-select" class="form-label">Выберите пользователя *</label>
                                    <select class="form-select form-select-ctf" id="existing-user-select" name="user_id" required>
                                        <option value="">-- Выберите пользователя --</option>
                                        <?php $__currentLoopData = $availableUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $availableUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($availableUser->id); ?>">
                                                <?php echo e($availableUser->surname); ?> <?php echo e($availableUser->name); ?> <?php echo e($availableUser->patronymic); ?> 
                                                (<?php echo e($availableUser->email); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="form-text">
                                        После добавления пользователь получит email с временным паролем для входа.
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-ctf-primary">
                                        <i class="bi bi-person-plus-fill me-2"></i><p class="text-muted">Добавить в команду</p>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Нет доступных пользователей без команды.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- Редактирование контактов -->
            <div class="card task-card mb-4">
                <div class="task-card-header">
                    <h4 class="mb-0">Редактировать контакты</h4>
                </div>
                <div class="task-card-body">
                    <form method="POST" action="<?php echo e(route('dashboard.update-contact')); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-ctf" 
                                   id="edit-email" name="email" 
                                   value="<?php echo e($user->email); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control form-control-ctf" 
                                   id="edit-phone" name="phone" 
                                   value="<?php echo e($user->phone); ?>">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-ctf">
                                <i class="bi bi-save me-2"></i>Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Смена пароля -->
            <div class="card task-card">
                <div class="task-card-header">
                    <h4 class="mb-0">Смена пароля</h4>
                </div>
                <div class="task-card-body">
                    <form method="POST" action="<?php echo e(route('dashboard.change-password')); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <?php if($user->temporary_password): ?>
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-key me-2"></i>
                            Вы используете временный пароль. Рекомендуется сменить его.
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="current-password" class="form-label">Текущий пароль</label>
                            <input type="password" class="form-control form-control-ctf" 
                                   id="current-password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new-password" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control form-control-ctf" 
                                   id="new-password" name="new_password" required 
                                   data-min-length="6">
                        </div>
                        
                        <div class="mb-3">
                            <label for="new-password-confirmation" class="form-label">Подтверждение</label>
                            <input type="password" class="form-control form-control-ctf" 
                                   id="new-password-confirmation" 
                                   name="new_password_confirmation" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-ctf-danger">
                                <i class="bi bi-arrow-repeat me-2"></i>Сменить пароль
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Статистика (если есть) -->
            <?php if($team): ?>
            <div class="card task-card mt-4">
                <div class="task-card-header">
                    <h4 class="mb-0">Статистика команды</h4>
                </div>
                <div class="task-card-body">
                    <div class="text-center">
                        <h2 class="text-primary"><?php echo e($team->tasks()->sum('score')); ?></h2>
                        <p class="text-muted">Всего баллов</p>
                    </div>
                    
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <h4 class="text-success"><?php echo e($team->tasks()->wherePivot('flag1_found', true)->count()); ?></h4>
                            <small>1-х флагов</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success"><?php echo e($team->tasks()->wherePivot('flag2_found', true)->count()); ?></h4>
                            <small>2-х флагов</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение между формами добавления
    const toggleButtons = document.querySelectorAll('.toggle-add-mode');
    const newUserForm = document.getElementById('add-new-user-form');
    const existingUserForm = document.getElementById('add-existing-user-form');
    
    if (toggleButtons.length && newUserForm && existingUserForm) {
        function setActiveMode(mode) {
            toggleButtons.forEach(btn => {
                if (btn.dataset.mode === mode) {
                    btn.classList.add('btn-primary');
                    btn.classList.remove('btn-outline-secondary');
                } else {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-outline-secondary');
                }
            });
            
            if (mode === 'new') {
                newUserForm.style.display = 'block';
                existingUserForm.style.display = 'none';
            } else {
                newUserForm.style.display = 'none';
                existingUserForm.style.display = 'block';
            }
        }
        
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                setActiveMode(this.dataset.mode);
            });
        });
        
        // Устанавливаем активный режим по умолчанию
        setActiveMode('new');
    }
    
    // Функция для отправки формы через AJAX
    window.submitFormAjax = function(form, options = {}) {
        return new Promise((resolve, reject) => {
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (options.successMessage) {
                        alert(options.successMessage);
                    }
                    resolve(data);
                } else {
                    reject(new Error(data.message || 'Произошла ошибка'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                reject(new Error(error.message || 'Ошибка соединения'));
            });
        });
    };

    // Функция для показа уведомлений
    window.showNotification = function(message, type) {
        alert(message);
    };

    // AJAX отправка формы добавления нового участника
    const addMemberForm = document.getElementById('add-member-form');
    if (addMemberForm) {
        addMemberForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            window.submitFormAjax(this, {
                successMessage: 'Участник зарегистрирован. На его email отправлены данные для входа.'
            })
            .then(response => {
                if (response.success) {
                    this.reset();
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(error => {
                window.showNotification(error.message, 'error');
            });
        });
    }
    
    // AJAX отправка формы добавления существующего участника
    const addExistingMemberForm = document.getElementById('add-existing-member-form');
    if (addExistingMemberForm) {
        addExistingMemberForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            window.submitFormAjax(this, {
                successMessage: 'Участник добавлен в команду. На его email отправлены данные для входа.'
            })
            .then(response => {
                if (response.success) {
                    const select = document.getElementById('existing-user-select');
                    if (select) select.value = '';
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(error => {
                window.showNotification(error.message, 'error');
            });
        });
    }

    // Инициализация Bootstrap tooltips
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/ctf-platform/resources/views/dashboard.blade.php ENDPATH**/ ?>