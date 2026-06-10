<?php $__env->startSection('title', 'Управление заданиями'); ?>

<?php $__env->startSection('styles'); ?>
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
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
            <?php if($tasks->isEmpty()): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tasks display-1"></i>
                    <h4 class="mt-3">Нет заданий</h4>
                    <p>Создайте первое задание, нажав кнопку "Создать задание"</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-ctf table-hover">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Целевой IP</th>
                                <th>Время (мин)</th>
                                <th>Флаги</th>
                                <th>Команд</th>
                                <th>Статус</th>
                                <th>Дата создания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-muted">
                                    <strong><?php echo e($task->name); ?></strong>
                                    <?php if($task->description): ?>
                                        <br>
                                        <small class="text-muted"><?php echo e(Str::limit($task->description, 50)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?php echo e($task->target_ip); ?></code>
                                    <?php if($task->subnet_mask): ?>
                                        <br>
                                        <small class="text-muted"><?php echo e($task->subnet_mask); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo e($task->time_limit_minutes); ?></td>
                                <td>
                                    <span class="badge bg-success">2 флага</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo e($task->teams->count()); ?></span>
                                </td>
                                <td>
                                    <?php if($task->is_active): ?>
                                        <span class="task-status-active">
                                            <i class="bi bi-check-circle"></i> Активно
                                        </span>
                                    <?php else: ?>
                                        <span class="task-status-inactive">
                                            <i class="bi bi-x-circle"></i> Неактивно
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo e($task->created_at->format('d.m.Y H:i')); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" data-bs-target="#editTaskModal<?php echo e($task->id); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" data-bs-target="#viewTaskModal<?php echo e($task->id); ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete(<?php echo e($task->id); ?>, '<?php echo e($task->name); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="delete-form-<?php echo e($task->id); ?>" 
                                          action="<?php echo e(route('admin.tasks.destroy', $task)); ?>" 
                                          method="POST" class="d-none">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    
                    <!-- Пагинация -->
                    <?php if($tasks->hasPages()): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($tasks->links()); ?>

                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модальные окна ВНЕ таблицы -->
<?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<!-- Модальное окно просмотра задания -->
<div class="modal fade" id="viewTaskModal<?php echo e($task->id); ?>" tabindex="-1" aria-labelledby="viewTaskModalLabel<?php echo e($task->id); ?>" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-black modal-title" id="viewTaskModalLabel<?php echo e($task->id); ?>">Задание: <?php echo e($task->name); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-black">Основная информация:</h6>
                        <p class="text-black"><strong>IP адрес:</strong> <?php echo e($task->target_ip); ?></p>
                        <?php if($task->subnet_mask): ?>
                            <p class="text-black"><strong>Подсеть:</strong> <?php echo e($task->subnet_mask); ?></p>
                        <?php endif; ?>
                        <p class="text-black"><strong>Время на выполнение:</strong> <?php echo e($task->time_limit_minutes); ?> минут</p>
                        <p class="text-black"><strong>Статус:</strong> 
                            <?php if($task->is_active): ?>
                                <span class="badge bg-success">Активно</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Неактивно</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-black">Флаги:</h6>
                        <div class="text-black mb-3">
                            <strong>Флаг 1:</strong>
                            <div class="text-black flag-preview">Хэш: <?php echo e(Str::limit($task->flag1_hash, 30)); ?>...</div>
                        </div>
                        <div class="mb-3">
                            <strong>Флаг 2:</strong>
                            <div class="text-black flag-preview">Хэш: <?php echo e(Str::limit($task->flag2_hash, 30)); ?>...</div>
                        </div>
                    </div>
                </div>
                
                <?php if($task->description): ?>
                    <h6 class="text-black mt-3">Описание:</h6>
                    <p class="text-black"><?php echo e($task->description); ?></p>
                <?php endif; ?>
                
                <h6 class="text-black mt-4">Назначенные команды (<?php echo e($task->teams->count()); ?>):</h6>
                <?php if($task->teams->isEmpty()): ?>
                    <p class="text-black">Задание не назначено ни одной команде</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Команда</th>
                                    <th>Прогресс</th>
                                    <th>Баллы</th>
                                    <th>Окончание</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $task->teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pivot = $team->pivot ?? null;
                                ?>
                                <tr>
                                    <td><?php echo e($team->name); ?></td>
                                    <td>
                                        <?php if($pivot): ?>
                                            <?php if($pivot->flag1_found && $pivot->flag2_found): ?>
                                                <span class="badge bg-success">✅ Все флаги</span>
                                            <?php elseif($pivot->flag1_found || $pivot->flag2_found): ?>
                                                <span class="badge bg-warning">⏳ В процессе</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">❌ Не начато</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">❌ Не начато</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($pivot->score ?? 0); ?></td>
                                    <td>
                                        <?php if($pivot && $pivot->end_time): ?>
                                            <?php echo e(date('H:i', strtotime($pivot->end_time))); ?>

                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования задания -->
<div class="modal fade" id="editTaskModal<?php echo e($task->id); ?>" tabindex="-1" aria-labelledby="editTaskModalLabel<?php echo e($task->id); ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('admin.tasks.update', $task)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header">
                    <h5 class="text-black modal-title" id="editTaskModalLabel<?php echo e($task->id); ?>">Редактировать задание</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name<?php echo e($task->id); ?>" class="text-black form-label">Название *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="name<?php echo e($task->id); ?>" name="name" 
                                   value="<?php echo e($task->name); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time_limit_minutes<?php echo e($task->id); ?>" class="text-black form-label">
                                Время (минут) *
                            </label>
                            <input type="number" class="form-control form-control-ctf" 
                                   id="time_limit_minutes<?php echo e($task->id); ?>" name="time_limit_minutes" 
                                   value="<?php echo e($task->time_limit_minutes); ?>" min="1" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="target_ip<?php echo e($task->id); ?>" class="text-black form-label">IP адрес *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="target_ip<?php echo e($task->id); ?>" name="target_ip" 
                                   value="<?php echo e($task->target_ip); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subnet_mask<?php echo e($task->id); ?>" class="text-black form-label">Подсеть</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="subnet_mask<?php echo e($task->id); ?>" name="subnet_mask" 
                                   value="<?php echo e($task->subnet_mask); ?>">
                        </div>
                    </div>
                    
		   <div class="row">
			    <div class="col-md-6 mb-3">
			        <label for="vm_id<?php echo e($task->id); ?>" class="text-black form-label">ID виртуальной машины (Proxmox)</label>
			        <input type="number" class="form-control form-control-ctf" 
			               id="vm_id<?php echo e($task->id); ?>" name="vm_id" 
			               value="<?php echo e($task->vm_id); ?>" min="100" max="9999">
			    </div>
			    <div class="col-md-6 mb-3">
			        <label for="proxmox_node<?php echo e($task->id); ?>" class="text-black form-label">Нода Proxmox</label>
			        <input type="text" class="form-control form-control-ctf" 
			               id="proxmox_node<?php echo e($task->id); ?>" name="proxmox_node" 
			               value="<?php echo e($task->proxmox_node ?? 'pve'); ?>">
			    </div>
			</div>

                    <div class="mb-3">
                        <label for="description<?php echo e($task->id); ?>" class="text-black form-label">Описание</label>
                        <textarea class="form-control form-control-ctf" 
                                  id="description<?php echo e($task->id); ?>" name="description" 
                                  rows="3"><?php echo e($task->description); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   id="is_active<?php echo e($task->id); ?>" name="is_active" 
                                   value="1" <?php echo e($task->is_active ? 'checked' : ''); ?>>
                            <label class="text-black form-check-label" for="is_active<?php echo e($task->id); ?>">
                                Активное задание
                            </label>
                        </div>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<!-- Модальное окно создания задания -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('admin.tasks.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class=" text-black modal-title" id="createTaskModalLabel">Создать задание</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="task_name" class="text-black form-label">Название *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="task_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time_limit_minutes" class="text-black form-label">Время (минут) *</label>
                            <input type="number" class="form-control form-control-ctf" 
                                   id="time_limit_minutes" name="time_limit_minutes" 
                                   min="1" value="60" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="target_ip" class="text-black form-label">IP адрес *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="target_ip" name="target_ip" 
                                   placeholder="192.168.1.100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subnet_mask" class="text-black form-label">Подсеть</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="subnet_mask" name="subnet_mask" 
                                   placeholder="255.255.255.0">
                        </div>
                    </div>

		    <div class="row">
			<div class="col-md-6 mb-3">
		    	    <label for="vm_id" class="text-black form-label">ID виртуальной машины (Proxmox)</label>
			    <input type="number" class="form-control form-control-ctf" 
		                id="vm_id" name="vm_id" 
		                placeholder="100" min="100" max="9999">
		            <div class="text-black form-text">Уникальный ID ВМ в Proxmox (обычно от 100)</div>
		        </div>
		        <div class="col-md-6 mb-3">
		            <label for="proxmox_node" class="text-black form-label">Нода Proxmox</label>
		            <input type="text" class="form-control form-control-ctf" 
		                id="proxmox_node" name="proxmox_node" 
		                placeholder="pve" value="pve">
		            <div class="text-black form-text">Имя ноды (обычно 'pve')</div>
		        </div>
		    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="text-black form-label">Описание</label>
                        <textarea class="form-control form-control-ctf" 
                                  id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="flag1_value" class="text-black form-label">Флаг 1 *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="flag1_value" name="flag1_value" 
                                   placeholder="CTF{first_flag_example}" required>
                            <div class="text-black form-text">Будет автоматически захэширован</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="flag2_value" class="text-black form-label">Флаг 2 *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="flag2_value" name="flag2_value" 
                                   placeholder="CTF{second_flag_example}" required>
                            <div class="text-black form-text">Будет автоматически захэширован</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="teams" class="text-black form-label">Назначить командам</label>
                        <select class="form-select form-control-ctf" id="teams" name="teams[]" multiple>
                            <?php
                                $teams = $teams ?? [];
                            ?>
                            <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($team->id); ?>"><?php echo e($team->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="text-black form-text">
                            Удерживайте Ctrl для выбора нескольких команд
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function confirmDelete(taskId, taskName) {
    if (confirm(`Удалить задание "${taskName}"?\n\nЗадание будет удалено у всех команд.`)) {
        document.getElementById(`delete-form-${taskId}`).submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация мультиселекта
    const teamsSelect = document.getElementById('teams');
    if (teamsSelect) {
        // Проверяем наличие библиотеки Choices
        if (typeof Choices !== 'undefined') {
            new Choices(teamsSelect, {
                removeItemButton: true,
                searchEnabled: true,
                placeholder: true,
                placeholderValue: 'Выберите команды',
                searchPlaceholderValue: 'Поиск команд...'
            });
        }
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/ctf-platform/resources/views/admin/tasks.blade.php ENDPATH**/ ?>