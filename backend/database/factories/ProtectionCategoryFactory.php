<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProtectionCategory>
 */
class ProtectionCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'key' => Str::slug($name, '_').'_'.Str::random(4),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'icon' => null,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
