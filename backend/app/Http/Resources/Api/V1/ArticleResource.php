<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full article payload for the detail endpoint.
 *
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isProviderSpecific = $this->isProviderSpecific();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'key_takeaways' => $this->key_takeaways ?? [],
            'level' => $this->level?->value,
            'estimated_reading_minutes' => $this->estimated_reading_minutes,
            'is_featured' => (bool) $this->is_featured,
            'knowledge_type' => $isProviderSpecific ? 'provider_specific' : 'general',
            'category' => new CategorySummaryResource($this->whenLoaded('category')),
            'provider' => new ProviderSummaryResource($this->whenLoaded('provider')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            // Glossary terms explicitly linked to this article (curated) — powers
            // the in-article :term[] tooltips and the "terms in this article" list.
            'glossary_terms' => $this->whenLoaded('glossaryTerms', fn () => $this->glossaryTerms
                ->map(fn ($t) => [
                    'term' => $t->term,
                    'slug' => $t->slug,
                    'short_definition' => $t->short_definition,
                ])->values()),

            // Deterministic "Pelajari Selanjutnya".
            'related' => ArticleSummaryResource::collection($this->whenLoaded('relatedArticles')),

            // Trust metadata (ADR-0004). Always present as keys; meaningful for
            // provider-specific content, may be null for general knowledge.
            'trust' => [
                'is_provider_specific' => $isProviderSpecific,
                'reference_status' => $this->reference_status?->value,
                'reference_period' => $this->reference_period,
                'last_reviewed_at' => optional($this->last_reviewed_at)->toDateString(),
            ],
            'sources' => SourceResource::collection($this->whenLoaded('sources', fn () => $this->sources->where('access_level', 'public')->values())),

            'published_at' => optional($this->published_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
