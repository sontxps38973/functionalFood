<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - Quyền cao nhất
        Admin::updateOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('superadmin123'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        // Admin quản lý sản phẩm
        Admin::updateOrCreate([
            'email' => 'product.admin@example.com',
        ], [
            'name' => 'Product Manager',
            'password' => Hash::make('product123'),
            'role' => 'product_admin',
            'status' => 'active',
        ]);

        // Admin quản lý đơn hàng
        Admin::updateOrCreate([
            'email' => 'order.admin@example.com',
        ], [
            'name' => 'Order Manager',
            'password' => Hash::make('order123'),
            'role' => 'order_admin',
            'status' => 'active',
        ]);

        // Admin quản lý khách hàng
        Admin::updateOrCreate([
            'email' => 'customer.admin@example.com',
        ], [
            'name' => 'Customer Manager',
            'password' => Hash::make('customer123'),
            'role' => 'customer_admin',
            'status' => 'active',
        ]);

        // Admin quản lý marketing (coupons, events)
        Admin::updateOrCreate([
            'email' => 'marketing.admin@example.com',
        ], [
            'name' => 'Marketing Manager',
            'password' => Hash::make('marketing123'),
            'role' => 'marketing_admin',
            'status' => 'active',
        ]);

        // Admin quản lý nội dung (posts, reviews)
        Admin::updateOrCreate([
            'email' => 'content.admin@example.com',
        ], [
            'name' => 'Content Manager',
            'password' => Hash::make('content123'),
            'role' => 'content_admin',
            'status' => 'active',
        ]);

        // Admin quản lý tài chính
        Admin::updateOrCreate([
            'email' => 'finance.admin@example.com',
        ], [
            'name' => 'Finance Manager',
            'password' => Hash::make('finance123'),
            'role' => 'finance_admin',
            'status' => 'active',
        ]);

        // Admin hỗ trợ khách hàng
        Admin::updateOrCreate([
            'email' => 'support.admin@example.com',
        ], [
            'name' => 'Customer Support',
            'password' => Hash::make('support123'),
            'role' => 'support_admin',
            'status' => 'active',
        ]);

        // Admin quản lý hệ thống
        Admin::updateOrCreate([
            'email' => 'system.admin@example.com',
        ], [
            'name' => 'System Administrator',
            'password' => Hash::make('system123'),
            'role' => 'system_admin',
            'status' => 'active',
        ]);

        // Admin thực tập sinh
        Admin::updateOrCreate([
            'email' => 'intern.admin@example.com',
        ], [
            'name' => 'Admin Intern',
            'password' => Hash::make('intern123'),
            'role' => 'intern',
            'status' => 'active',
        ]);

        $this->command->info('Đã tạo thành công ' . Admin::count() . ' tài khoản admin!');
        $this->command->info('Thông tin đăng nhập:');
        $this->command->info('1. Super Admin: superadmin@example.com / superadmin123');
        $this->command->info('2. Product Manager: product.admin@example.com / product123');
        $this->command->info('3. Order Manager: order.admin@example.com / order123');
        $this->command->info('4. Customer Manager: customer.admin@example.com / customer123');
        $this->command->info('5. Marketing Manager: marketing.admin@example.com / marketing123');
        $this->command->info('6. Content Manager: content.admin@example.com / content123');
        $this->command->info('7. Finance Manager: finance.admin@example.com / finance123');
        $this->command->info('8. Customer Support: support.admin@example.com / support123');
        $this->command->info('9. System Admin: system.admin@example.com / system123');
        $this->command->info('10. Admin Intern: intern.admin@example.com / intern123');
    }
}
