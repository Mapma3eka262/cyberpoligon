<?php $__env->startComponent('mail::message'); ?>
# Добро пожаловать в команду <?php echo e($teamName); ?>!

Здравствуйте, <?php echo e($participantName); ?>!

Капитан команды **<?php echo e($teamName); ?>** добавил вас в состав участников CTF-соревнования.

**Ваши данные для входа:**
- **Логин:** будет сгенерирован автоматически при первом входе
- **Временный пароль:** `<?php echo e($temporaryPassword); ?>`

**Инструкции:**
1. Перейдите на сайт CTF-платформы
2. Нажмите "Вход/Регистрация"
3. Используйте временный пароль для входа
4. Смените пароль после первого входа

<?php $__env->startComponent('mail::button', ['url' => route('login.register')]); ?>
Войти на платформу
<?php echo $__env->renderComponent(); ?>

**Важно:** После первого входа вы сможете сменить временный пароль на свой собственный.

Если у вас возникли проблемы со входом, пожалуйста, свяжитесь с капитаном вашей команды.

Удачи на соревнованиях! 🚩

С уважением,<br>
Команда CTF Platform
<?php echo $__env->renderComponent(); ?><?php /**PATH /var/www/ctf-platform/resources/views/emails/team-invitation.blade.php ENDPATH**/ ?>