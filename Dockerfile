# Base image PHP + Apache
FROM php:8.2-apache

# Cài extension cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Bật mod_rewrite cho Apache
RUN a2enmod rewrite

# Cấu hình Apache để trỏ vào thư mục public
COPY docker/laravel.conf /etc/apache2/sites-available/000-default.conf

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy source code
COPY . .

# Cài dependencies Laravel
RUN composer install --no-dev --optimize-autoloader

# Phân quyền cho storage và cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy file entrypoint
COPY docker/entrypoint.sh /var/www/html/docker/entrypoint.sh
RUN chmod +x /var/www/html/docker/entrypoint.sh

# Copy file cấu hình Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose cổng 80
EXPOSE 80

# Chạy entrypoint khi container start
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
