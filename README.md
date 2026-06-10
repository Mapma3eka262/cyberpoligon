# Инструкция по развертыванию Cyberpoligon

## Требования

- Ubuntu 24.04 LTS
- PHP 8.3+
- MySQL 8.0+
- Apache 2.4+
- Composer 2.0+
- Node.js 18+ (для сборки фронтенда)

## 1. Установка базового ПО

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка PHP и расширений
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring \
php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath

# Установка Apache
sudo apt install -y apache2 libapache2-mod-php8.3

# Установка MySQL
sudo apt install -y mysql-server mysql-client

# Установка Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Установка Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

## 2. Настройка базы данных
```bash
# Вход в MySQL
sudo mysql

# Создание базы данных
CREATE DATABASE ctf_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Создание пользователя
CREATE USER 'ctf_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON ctf_platform.* TO 'ctf_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3. Развертывание приложения
```bash
# Клонирование репозитория
cd /var/www
sudo git clone https://github.com/Mapma3eka262/ctf-platform.git
cd ctf-platform

# Настройка прав доступа
sudo chown -R www-data:www-data /var/www/ctf-platform
sudo chmod -R 755 /var/www/ctf-platform/storage
sudo chmod -R 755 /var/www/ctf-platform/bootstrap/cache

# Копирование файла окружения отредактировать существующий
cp .env.example .env

# Редактирование .env файла
nano .env
Настройки в .env:

.env
APP_NAME="CTF Platform"
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Yekaterinburg

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ctf_platform
DB_USERNAME=ctf_user
DB_PASSWORD=secure_password_here
DB_FOREIGN_KEYS=false

# Настройки почты
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-email-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="CTF Platform"

# Настройка proxmox
PROXMOX_HOST=https://your-domain.com:8006
PROXMOX_NODE=pve
PROXMOX_TOKEN_NAME=
PROXMOX_TOKEN_SECRET=
PROXMOX_VERIFY_SSL=false
```

## 4. Установка зависимостей

```bash
# Установка PHP зависимостей
cd /var/www/ctf-platform
composer install --optimize-autoloader --no-dev

# Установка NPM зависимостей
npm install

# Сборка фронтенда
npm run production

# Генерация ключа приложения
php artisan key:generate

# Настройка хранилища
php artisan storage:link
```

## 5. Настройка базы данных
```bash
# Запуск миграций
php artisan migrate --force

# Запуск сидеров (создание администратора)
php artisan db:seed --force

# Оптимизация приложения
php artisan optimize
php artisan view:cache
php artisan route:cache
php artisan config:cache
```

## 6. Настройка Apache
```bash
# Создание конфигурационного файла
sudo nano /etc/apache2/sites-available/ctf-platform.conf
```
Содержимое конфигурации:

```apache
<VirtualHost *:80>
    ServerName ctfarena.scanbit.ru
    # Перенаправление HTTP на HTTPS
    Redirect permanent / https://ctfarena.scanbit.ru/
</VirtualHost>

<VirtualHost *:443>
    ServerName ctfarena.scanbit.ru
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/ctf-platform/public

    # Директива для включения SSL
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/scanbit.crt
    SSLCertificateKeyFile /etc/ssl/private/scanbit.key

    <Directory /var/www/ctf-platform/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ctf-platform-error.log
    CustomLog ${APACHE_LOG_DIR}/ctf-platform-access.log combined
</VirtualHost>
```

```bash
# Активация сайта
sudo a2ensite ctf-platform.conf
sudo a2dissite 000-default.conf

# Включение модулей Apache
sudo a2enmod ssl
sudo a2enmod rewrite
sudo a2enmod headers

# Перезагрузка Apache
sudo systemctl reload apache2
```

## 7. Настройка планировщика задач (Cron)
```bash
# Редактирование crontab
sudo crontab -e
```
Добавить строки:

```text
* * * * * cd /var/www/ctf-platform && php artisan schedule:run >> /dev/null 2>&1
0 0 * * * cd /var/www/ctf-platform && php artisan metrics:collect >> /var/log/ctf-metrics.log 2>&1
```

## 8. Настройка файрвола
```bash
# Разрешение HTTP/HTTPS
sudo ufw allow 'Apache Full'
sudo ufw allow ssh

# Включение файрвола
sudo ufw enable
```

## 9. Запуск и проверка
```bash
# Проверка конфигурации
php artisan route:list
php artisan migrate:status

# Проверка доступности
curl -I http://localhost
```

## 10. Административный доступ
По умолчанию создается администратор:

Логин: admin
Пароль: admin

После первого входа обязательно смените пароль!

## 11. Мониторинг и обслуживание
Просмотр логов:
```bash
# Логи приложения
tail -f storage/logs/laravel.log

# Логи Apache
tail -f /var/log/apache2/ctf-platform-error.log

# Логи метрик
tail -f /var/log/ctf-metrics.log
```

Обновление приложения:
```bash
cd /var/www/ctf-platform
sudo git pull origin main
composer install --optimize-autoloader --no-dev
npm run production
php artisan migrate --force
php artisan optimize
sudo systemctl reload apache2
```

Резервное копирование базы данных:
```bash
# Ежедневное резервное копирование (добавить в cron)
mysqldump -u ctf_user -p ctf_platform > /backup/ctf-platform-$(date +%Y%m%d).sql
```

### Устранение неполадок
### 1. Ошибка "Permission denied"
```bash
sudo chown -R www-data:www-data /var/www/ctf-platform
sudo chmod -R 755 storage bootstrap/cache
```

### 2. Ошибка базы данных
```bash
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan config:clear
```
### 3. Ошибка отправки email
Проверьте настройки в ```.env``` и убедитесь, что почтовый сервер работает.

### 4. Ошибка "Class not found"
```bash
composer dump-autoload
php artisan optimize
```

# Безопасность
1. Измените пароль администратора после первого входа
2. Настройте HTTPS с помощью Let's Encrypt
3. Регулярно обновляйте зависимости: composer update
4. Настройте брандмауэр для ограничения доступа
5. Настройте мониторинг системы и логов
6. Регулярно делайте резервные копии базы данных
