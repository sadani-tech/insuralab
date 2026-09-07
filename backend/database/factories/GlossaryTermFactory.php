<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GlossaryTerm>
 */
class GlossaryTermFactory extends Factory
{
    public function definition(): array
    {
        $term = Str::title(fake()->unique()->words(2, true));

        return [
            'provider_id' => null,
            'term' => $term,
            'slug' => Str::slug($term).'-'.Str::random(4),
            'short_definition' => fake()->sentence(10),
            'definition' => fake()->paragraphs(2, true),
            'category' => 'general',
            'metadata' => null,
            'last_reviewed_at' => null,
            'reference_status' => null,
        ];
    }
}
