<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Source>
 */
class SourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => Str::title(fake()->words(4, true)),
            'publisher' => fake()->company(),
            'url' => fake()->url(),
            'published_at' => now()->subYears(fake()->numberBetween(0, 3)),
            'accessed_at' => now(),
            'reference_period' => 'Compilation, '.now()->format('F Y'),
            'notes' => null,
            'access_level' => 'public',
        ];
    }
}
