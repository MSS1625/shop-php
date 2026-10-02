<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;

        return [
            'category_id' => Category::factory(),
            'title' => 'محصول تستی '.++$counter,
            'slug' => 'test-product-'.$counter.'-'.$this->faker->unique()->numberBetween(1000, 99999),
            'description' => 'توضیحات تستی برای محصول',
            'price' => $this->faker->numberBetween(100000, 5000000),
            'stock' => $this->faker->numberBetween(1, 50),
            'is_active' => true,
            'image' => null,
        ];
    }
}
