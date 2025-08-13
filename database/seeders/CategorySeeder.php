<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Sinh lý – Nội tiết',
            'Thải độc – Gan',
            'Tiêu hóa – Dạ dày',
            'Xương khớp',
            'Tim mạch – Huyết áp',
            'Thần kinh – Giấc ngủ',
            'Đái tháo đường',
            'Ung bướu – Miễn dịch',
        ];

        foreach ($categories as $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name)
            ]);
        }
    }
}

