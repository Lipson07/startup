FROM php:8.4-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ШАГ 1: Копируем ТОЛЬКО composer файлы
COPY composer.json composer.lock ./

# ШАГ 2: Устанавливаем зависимости БЕЗ запуска скриптов
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# ШАГ 3: Копируем весь код приложения
COPY . .

# ШАГ 4: ВРУЧНУЮ запускаем скрипт post-autoload-dump (теперь artisan доступен)
RUN composer run-script post-autoload-dump

# ШАГ 5: Устанавливаем права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ШАГ 6: Оптимизируем Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 9000
CMD ["php-fpm"]
