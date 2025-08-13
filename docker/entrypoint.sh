#!/bin/bash

# Nếu APP_KEY chưa set, tạo key tạm
if [ -z "$(php artisan key:generate --show)" ]; then
    php artisan key:generate
fi

# Chạy migration tự động
php artisan migrate --force

# Cache config và route
php artisan config:cache
php artisan route:cache

# Khởi động supervisor (Apache + Queue worker)
exec /usr/bin/supervisord
