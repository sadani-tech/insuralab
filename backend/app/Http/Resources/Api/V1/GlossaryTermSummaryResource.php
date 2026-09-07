<?php

namespace App\Http\Resources\Api\V1;

use App\Models\GlossaryTerm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Glossary term shape for the list endpoint — tooltip-length only, no full
 * definition or related terms.
 *
 * @mixin GlossaryTerm
 */
class GlossaryTermSummaryResource extends JsonResource
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
            'category' => $this->category,
            'letter' => mb_strtoupper(mb_substr($this->term, 0, 1)),
            'is_provider_specific' => $this->provider_id !== null,
            'reference_status' => $this->reference_status?->value,
            'articles_count' => $this->whenNotNull($this->articles_count ?? null),
        ];
    }
}
