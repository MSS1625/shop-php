<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = 'دسته تستی '.$this->faker->unique()->numberBetween(1, 1000);

        return [
            'name' => $name,
            'slug' => Str::slug($this->faker->unique()->slug(2)),
            'icon' => 'fa-tag',
        ];
    }
}
