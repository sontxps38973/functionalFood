# Base image: PHP + Apache
FROM php:8.2-apache

# Cài đặt các extension cần cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy source code
COPY . .

# Cài dependencies Laravel
RUN composer install --no-dev --optimize-autoloader

# Phân quyền cho storage và bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Cache config và route
RUN php artisan config:cache && php artisan route:cache

# Copy file cấu hình Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose cổng 80 cho Apache
EXPOSE 80

# Chạy Supervisor để quản lý Apache + Queue
ENTRYPOINT ["sh", "/var/www/html/docker/entrypoint.sh"]
CMD ["/usr/bin/supervisord"]

