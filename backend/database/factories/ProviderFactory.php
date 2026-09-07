<?php

namespace Database\Factories;

use App\Enums\ProviderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->paragraph(),
            'country_of_origin' => fake()->country(),
            'presence_in_indonesia' => fake()->sentence(),
            'website_url' => fake()->url(),
            'status' => ProviderStatus::Active,
            'last_reviewed_at' => now()->subDays(fake()->numberBetween(1, 90)),
            'metadata' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => ProviderStatus::Inactive]);
    }
}
