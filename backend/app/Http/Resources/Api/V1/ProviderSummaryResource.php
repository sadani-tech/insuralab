<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lightweight provider shape for list endpoints and for nesting inside article /
 * product payloads. No provider is privileged (ADR-0002) — this is just "the
 * provider" as a variable.
 *
 * @mixin Provider
 */
class ProviderSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'country_of_origin' => $this->country_of_origin,
            'last_reviewed_at' => optional($this->last_reviewed_at)->toDateString(),
            'products_count' => $this->whenNotNull($this->products_count ?? null),
            'articles_count' => $this->whenNotNull($this->articles_count ?? null),
        ];
    }
}
