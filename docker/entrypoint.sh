#!/bin/bash
set -e

echo "🚀 Starting Laravel setup..."

# Tạo APP_KEY nếu chưa có
if [ -z "$(php artisan key:generate --show)" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate
fi

# Phân quyền thư mục cần thiết
echo "📂 Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Tạo storage link nếu chưa có
echo "🔗 Linking storage..."
php artisan storage:link || true

# Xoá cache cũ
echo "🧹 Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache lại
echo "⚡ Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate Swagger docs
echo "📜 Generating Swagger docs..."
php artisan l5-swagger:generate || true

# Chạy migration
echo "🗄 Running migrations..."
php artisan migrate --force

echo "✅ Laravel ready. Starting Supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
