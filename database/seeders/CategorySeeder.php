<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * دسته‌بندی‌های اولیه فروشگاه
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'ابزار شبکه', 'slug' => 'tools', 'icon' => 'fa-screwdriver-wrench'],
            ['name' => 'مودم و روتر', 'slug' => 'modems-routers', 'icon' => 'fa-wifi'],
            ['name' => 'سوییچ و هاب', 'slug' => 'switches-hubs', 'icon' => 'fa-network-wired'],
            ['name' => 'کابل و کانکتور', 'slug' => 'cables-connectors', 'icon' => 'fa-plug'],
            ['name' => 'لوازم جانبی', 'slug' => 'accessories', 'icon' => 'fa-shield-halved'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
