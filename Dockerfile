# Dùng PHP 8.2 + Composer
FROM php:8.2-fpm

# Cài extensions cần cho Laravel
RUN apt-get update && apt-get install -y \
    zip unzip git curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Cài Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copy source code
WORKDIR /var/www/html
COPY . .

# Cài dependency
RUN composer install --no-dev --optimize-autoloader

# Set quyền
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose cổng 8000 và chạy Laravel
EXPOSE 8000
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
