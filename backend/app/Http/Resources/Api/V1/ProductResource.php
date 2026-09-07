<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full product payload for the detail endpoint.
 *
 * The JSONB `metadata` column is NOT returned wholesale — only an explicit
 * allow-list of public keys is exposed (ADR-0002: JSONB is for optional public
 * metadata, but the API must not leak anything added there later).
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Product `metadata` keys safe to expose publicly.
     *
     * @var list<string>
     */
    public const PUBLIC_METADATA_KEYS = [
        'payout_model',
        'tiers',
        'reduces_base_sum_assured',
        'variants',
        'is_paydi',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'insurance_scheme' => $this->insurance_scheme?->value,
            'product_structure' => $this->product_structure?->value,

            'provider' => new ProviderSummaryResource($this->whenLoaded('provider')),
            'product_category' => new ProductCategoryResource($this->whenLoaded('productCategory')),
            'protection_categories' => ProtectionCategoryResource::collection($this->whenLoaded('protectionCategories')),

            // Concept-first (ADR-0002): the general articles + glossary terms a
            // reader should understand before this provider product.
            'related_articles' => ArticleSummaryResource::collection($this->whenLoaded('relatedArticles')),
            'related_terms' => $this->whenLoaded('glossaryTerms', fn () => $this->glossaryTerms
                ->map(fn ($t) => [
                    'term' => $t->term,
                    'slug' => $t->slug,
                    'short_definition' => $t->short_definition,
                ])->values()),

            'metadata' => (object) $this->publicMetadata(),

            // Trust metadata (ADR-0004) — always present for products.
            'trust' => [
                'reference_status' => $this->reference_status?->value,
                'reference_period' => $this->reference_period,
                'last_reviewed_at' => optional($this->last_reviewed_at)->toDateString(),
            ],
            'sources' => SourceResource::collection($this->whenLoaded('sources')),

            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function publicMetadata(): array
    {
        $metadata = $this->metadata ?? [];

        return array_intersect_key($metadata, array_flip(self::PUBLIC_METADATA_KEYS));
    }
}
