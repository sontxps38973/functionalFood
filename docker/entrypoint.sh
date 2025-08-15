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
php artisan cache:clear

# Cache lại
echo "⚡ Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate Swagger docs
echo "📜 Generating Swagger docs..."
php artisan l5-swagger:generate || true

# Kiểm tra database connection
echo "🔍 Checking database connection..."
php artisan tinker --execute="
    try {
        \DB::connection()->getPdo();
        echo '✅ Database connected successfully';
    } catch (\Exception \$e) {
        echo '❌ Database connection failed: ' . \$e->getMessage();
        exit(1);
    }
" || exit 1

# Chạy migration
echo "🗄 Running migrations..."
php artisan migrate --force || {
    echo "⚠️ Migration failed, but continuing..."
}

# Chạy seeding để tạo data cần thiết
echo "🌱 Running seeders..."
php artisan db:seed --force || {
    echo "⚠️ Seeding failed, trying individual seeders..."
    
    # Chạy từng seeder riêng lẻ
    php artisan db:seed --class=CustomerRankSeeder --force || echo "❌ CustomerRankSeeder failed"
    php artisan db:seed --class=CategorySeeder --force || echo "❌ CategorySeeder failed"
    php artisan db:seed --class=ProductSeeder --force || echo "❌ ProductSeeder failed"
    php artisan db:seed --class=AdminSeeder --force || echo "❌ AdminSeeder failed"
}

# Tạo tài khoản admin mặc định nếu chưa có
echo "👑 Creating default admin account..."
php artisan tinker --execute="
    if (!\App\Models\Admin::where('email', 'admin@functionalFood.com')->exists()) {
        \App\Models\Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@functionalFood.com',
            'password' => bcrypt('admin123'),
            'role' => 'super_admin',
            'status' => 'active',
            'last_login' => now()
        ]);
        echo 'Default admin created: admin@functionalFood.com / admin123';
    } else {
        echo 'Admin account already exists';
    }
" || true


# Fallback: Tạo data cơ bản nếu seeding hoàn toàn thất bại
echo "🔄 Creating fallback data..."
php artisan tinker --execute="
    try {
        // Tạo customer ranks cơ bản
        if (!\App\Models\CustomerRank::exists()) {
            \App\Models\CustomerRank::create(['name' => 'Bronze', 'level' => 1, 'min_total_spent' => 0]);
            \App\Models\CustomerRank::create(['name' => 'Silver', 'level' => 2, 'min_total_spent' => 1000000]);
            \App\Models\CustomerRank::create(['name' => 'Gold', 'level' => 3, 'min_total_spent' => 5000000]);
            echo '✅ Fallback customer ranks created';
        }
        
        // Tạo category cơ bản
        if (!\App\Models\Category::exists()) {
            \App\Models\Category::create(['name' => 'Thực phẩm chức năng', 'slug' => 'thuc-pham-chuc-nang', 'status' => 'active']);
            echo '✅ Fallback category created';
        }
        
        // Tạo product cơ bản
        if (!\App\Models\Product::exists()) {
            \$category = \App\Models\Category::first();
            if (\$category) {
                \App\Models\Product::create([
                    'category_id' => \$category->id,
                    'name' => 'Sản phẩm test',
                    'slug' => 'san-pham-test',
                    'description' => 'Sản phẩm test cho hệ thống',
                    'price' => 100000,
                    'stock_quantity' => 10,
                    'status' => 'active'
                ]);
                echo '✅ Fallback product created';
            }
        }
        
        echo '✅ Fallback data creation completed';
    } catch (\Exception \$e) {
        echo '❌ Fallback data creation failed: ' . \$e->getMessage();
    }
" || true

echo "🎉 Laravel setup completed!"

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
