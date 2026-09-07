<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\InsuranceScheme;
use App\Enums\ProductStructure;
use App\Enums\ReferenceStatus;
use App\Models\ProductCategory;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'provider_id' => Provider::factory(),
            'product_category_id' => ProductCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'insurance_scheme' => InsuranceScheme::Conventional,
            'product_structure' => ProductStructure::Other,
            'reference_status' => ReferenceStatus::NeedsReview,
            'reference_period' => 'Training compilation, August 2026',
            'last_reviewed_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'status' => ContentStatus::Draft,
            'metadata' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => ContentStatus::Published]);
    }

    public function historical(): static
    {
        return $this->state(fn () => ['reference_status' => ReferenceStatus::Historical]);
    }
}
