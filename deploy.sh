# 1. Устанавливаем Laravel
composer create-project laravel/laravel ctf-platform
cd ctf-platform

# 2. Устанавливаем дополнительные пакеты
composer require laravel/ui
php artisan ui bootstrap
npm install && npm run build

# 3. Создаем структуру каталогов
mkdir -p app/Http/Controllers/Admin
mkdir -p app/Mail
mkdir -p app/Middleware
mkdir -p resources/views/auth
mkdir -p resources/views/layouts
mkdir -p resources/views/admin
mkdir -p public/css
mkdir -p public/js
mkdir -p public/images
mkdir -p routes