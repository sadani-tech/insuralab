<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full provider profile. Products are NOT nested here — the frontend fetches
 * `/products?provider={slug}`. The few provider-specific articles (the "Materi
 * Edukasi" section) are included as summaries.
 *
 * @mixin Provider
 */
class ProviderResource extends JsonResource
{
    /**
     * Provider `metadata` keys safe to expose publicly.
     *
     * @var list<string>
     */
    public const PUBLIC_METADATA_KEYS = ['entities', 'business_areas', 'distribution'];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'country_of_origin' => $this->country_of_origin,
            'presence_in_indonesia' => $this->presence_in_indonesia,
            'website_url' => $this->website_url,
            'status' => $this->status?->value,
            'last_reviewed_at' => optional($this->last_reviewed_at)->toDateString(),
            'metadata' => (object) array_intersect_key(
                $this->metadata ?? [],
                array_flip(self::PUBLIC_METADATA_KEYS),
            ),
            'counts' => [
                'products' => (int) ($this->products_count ?? 0),
                'articles' => (int) ($this->articles_count ?? 0),
            ],
            'articles' => ArticleSummaryResource::collection($this->whenLoaded('articles')),
            'sources' => SourceResource::collection($this->whenLoaded('sources', fn () => $this->sources->where('access_level', 'public')->values())),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
