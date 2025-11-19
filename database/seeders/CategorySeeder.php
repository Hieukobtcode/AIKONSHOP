<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'ChatGPT Plus',
                'slug' => 'chatgpt-plus',
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'Mua Tài Khoản ChatGPT Plus Giá Rẻ Nhất Việt Nam 2025 - Bảo Hành 12 Tháng',
                'meta_description' => 'Shop bán tài khoản ChatGPT Plus chính chủ giá chỉ từ 149k/tháng. Bảo hành đầy đủ, hỗ trợ 24/7, thanh toán Momo, Banking, USDT. Có hóa đơn VAT.',
                'icon' => 'fab fa-openai',
            ],
            // Thêm các danh mục khác...
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}