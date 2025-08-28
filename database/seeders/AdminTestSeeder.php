<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tài khoản admin test đơn giản
        $testAdmins = [
            [
                'name' => 'Test Admin 1',
                'email' => 'admin1@test.com',
                'password' => 'admin123',
                'role' => 'admin',
                'status' => 'active'
            ],
            [
                'name' => 'Test Admin 2',
                'email' => 'admin2@test.com',
                'password' => 'admin123',
                'role' => 'admin',
                'status' => 'active'
            ],
            [
                'name' => 'Test Manager',
                'email' => 'manager@test.com',
                'password' => 'manager123',
                'role' => 'manager',
                'status' => 'active'
            ],
            [
                'name' => 'Test Supervisor',
                'email' => 'supervisor@test.com',
                'password' => 'supervisor123',
                'role' => 'supervisor',
                'status' => 'active'
            ]
        ];

        foreach ($testAdmins as $adminData) {
            Admin::updateOrCreate([
                'email' => $adminData['email'],
            ], [
                'name' => $adminData['name'],
                'password' => Hash::make($adminData['password']),
                'role' => $adminData['role'],
                'status' => $adminData['status'],
            ]);
        }

        $this->command->info('Đã tạo thành công ' . count($testAdmins) . ' tài khoản admin test!');
        $this->command->info('Thông tin đăng nhập test:');
        $this->command->info('1. Test Admin 1: admin1@test.com / admin123');
        $this->command->info('2. Test Admin 2: admin2@test.com / admin123');
        $this->command->info('3. Test Manager: manager@test.com / manager123');
        $this->command->info('4. Test Supervisor: supervisor@test.com / supervisor123');
    }
}
