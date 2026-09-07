<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Article shape for list endpoints. Body is intentionally omitted here.
 *
 * `knowledge_type` makes the general vs provider-specific split explicit in the
 * payload (ADR-0002). Provider-specific articles also carry trust metadata.
 *
 * @mixin Article
 */
class ArticleSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'level' => $this->level?->value,
            'estimated_reading_minutes' => $this->estimated_reading_minutes,
            'is_featured' => (bool) $this->is_featured,
            'knowledge_type' => $this->isProviderSpecific() ? 'provider_specific' : 'general',
            'category' => new CategorySummaryResource($this->whenLoaded('category')),
            'provider' => new ProviderSummaryResource($this->whenLoaded('provider')),
            'reference_status' => $this->reference_status?->value,
            'last_reviewed_at' => optional($this->last_reviewed_at)->toDateString(),
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
