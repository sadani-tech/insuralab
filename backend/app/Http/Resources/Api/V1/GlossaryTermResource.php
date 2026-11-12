<?php

namespace App\Http\Resources\Api\V1;

use App\Models\GlossaryTerm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full glossary term payload for the detail endpoint.
 *
 * @mixin GlossaryTerm
 */
class GlossaryTermResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'term' => $this->term,
            'slug' => $this->slug,
            'short_definition' => $this->short_definition,
            'definition' => $this->definition,
            'category' => $this->category,
            'letter' => mb_strtoupper(mb_substr($this->term, 0, 1)),
            'provider' => new ProviderSummaryResource($this->whenLoaded('provider')),
            'trust' => [
                'is_provider_specific' => $this->provider_id !== null,
                'reference_status' => $this->reference_status?->value,
                'last_reviewed_at' => optional($this->last_reviewed_at)->toDateString(),
            ],
            'related_terms' => $this->whenLoaded('relatedTerms', fn () => $this->relatedTerms->map(fn (GlossaryTerm $t) => [
                'term' => $t->term,
                'slug' => $t->slug,
                'short_definition' => $t->short_definition,
            ])->values()),
            'related_articles' => ArticleSummaryResource::collection($this->whenLoaded('articles')),
            'sources' => SourceResource::collection($this->whenLoaded('sources', fn () => $this->sources->where('access_level', 'public')->values())),
        ];
    }
}
