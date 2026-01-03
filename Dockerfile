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

# Устанавливаем Node.js 18
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ШАГ 1: Копируем ТОЛЬКО composer файлы
COPY backend/composer.json backend/composer.lock ./

# ШАГ 2: Устанавливаем зависимости БЕЗ запуска скриптов
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# ШАГ 3: Теперь копируем весь остальной код Laravel
COPY backend/ .

# ШАГ 4: ВРУЧНУЮ запускаем скрипт post-autoload-dump (теперь artisan доступен)
RUN composer run-script post-autoload-dump

# ШАГ 5: Устанавливаем права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ШАГ 6: Работа с фронтендом
WORKDIR /var/www/html/frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --no-audit
COPY frontend/ .
RUN npm run build

# ШАГ 7: Возвращаемся в корень и копируем фронтенд
WORKDIR /var/www/html
RUN cp -r frontend/dist/* public/

# ШАГ 8: Оптимизируем Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 9000
CMD ["php-fpm"]
