#!/bin/bash


echo "⚙️ Cài đặt composer packages..."
composer install

echo "📄 Tạo file .env nếu chưa có..."
cp .env.example .env 2>/dev/null || true

echo "🔑 Tạo APP_KEY..."
php artisan key:generate

echo "🗃️ Tạo database (nếu chưa tồn tại)..."
php artisan db:create

echo "📦 Chạy migrate và seed..."
php artisan migrate --seed

echo "📚 Tạo Swagger docs..."
php artisan l5-swagger:generate

echo "✅ Hoàn tất cài đặt."
