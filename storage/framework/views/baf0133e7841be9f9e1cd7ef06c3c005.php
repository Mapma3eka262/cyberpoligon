<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title'); ?> - CTF Platform</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS (будут добавлены стили из cyberpolygon) -->
    <link href="<?php echo e(asset('css/style.css')); ?>" rel="stylesheet">

    <a href="<?php echo e(route('privacy')); ?>" class="text-black" target="_blank">
    Политика конфиденциальности
    </a>
    
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
		<img src="<?php echo e(asset('images/logo.png')); ?>" alt="CTF Platform" style="height: 40px;">
	    </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>">Главная</a>
                    </li>
                    
                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('arena')); ?>">Арена</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('dashboard')); ?>">Личный кабинет</a>
                        </li>
                        
                        <?php if(auth()->user()->isAdmin()): ?>
			    <li class="nav-item dropdown">
			        <a class="nav-link dropdown-toggle" href="#" id="navbarAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
			            Админ-панель
			        </a>
			        <ul class="dropdown-menu" aria-labelledby="navbarAdminDropdown">
			            <li><a class="dropdown-item" href="<?php echo e(route('admin.teams')); ?>">Команды</a></li>
			            <li><a class="dropdown-item" href="<?php echo e(route('admin.tasks')); ?>">Задания</a></li>
			            <li><a class="dropdown-item" href="<?php echo e(route('admin.analytics')); ?>">Аналитика</a></li>
			            <li><a class="dropdown-item" href="<?php echo e(route('admin.stats')); ?>">Статистика</a></li>
			        </ul>
			    </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if(auth()->guard()->guest()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="<?php echo e(route('login.register')); ?>">Вход/Регистрация</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <span class="nav-link text-white">
                                <?php echo e(auth()->user()->name); ?> (<?php echo e(auth()->user()->team->name ?? 'Без команды'); ?>)
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="<?php echo e(route('logout')); ?>"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Выход
                            </a>
                            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                                <?php echo csrf_field(); ?>
                            </form>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <main class="py-4">
        <div class="container">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <!-- Футер -->
    <footer class="footer mt-auto py-3 bg-light border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-black">© Компания СКАНБИТ. Все права защищены</span>
                </div>
                <div class="col-md-6 text-end">
                    <a href="/privacy" class="text-black">Политика конфиденциальности</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo e(asset('js/script.js')); ?>"></script>
    
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/ctf-platform/resources/views/layouts/app.blade.php ENDPATH**/ ?>