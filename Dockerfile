# Sử dụng image PHP chính thức có Composer
FROM php:8.2-fpm

# Cài đặt các extension cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Tạo thư mục dự án
WORKDIR /var/www/html

# Sao chép file dự án vào container
COPY . .

# Cài đặt PHP dependencies qua Composer
RUN composer install --no-dev --optimize-autoloader

# Cài Node.js để build frontend nếu có
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run build || echo "Không có frontend để build"

# Clear cache Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose cổng chạy PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
