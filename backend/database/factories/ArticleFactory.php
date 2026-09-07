<?php

namespace Database\Factories;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Enums\ReferenceStatus;
use App\Models\Category;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(4), '.');

        return [
            'category_id' => Category::factory(),
            'provider_id' => null, // general knowledge by default
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(4),
            'excerpt' => fake()->sentence(12),
            'body' => fake()->paragraphs(4, true),
            'level' => ContentLevel::Beginner,
            'status' => ContentStatus::Draft,
            'is_featured' => false,
            'estimated_reading_minutes' => fake()->numberBetween(2, 9),
            'published_at' => null,
            'last_reviewed_at' => null,
            'reference_period' => null,
            'reference_status' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);
    }

    /** Provider-specific article, with the review metadata publishing requires. */
    public function forProvider(?Provider $provider = null): static
    {
        return $this->state(fn () => [
            'provider_id' => $provider?->id ?? Provider::factory(),
            'level' => ContentLevel::Advanced,
            'last_reviewed_at' => now()->subDays(10),
            'reference_period' => 'Training compilation, August 2026',
            'reference_status' => ReferenceStatus::NeedsReview,
        ]);
    }
}
