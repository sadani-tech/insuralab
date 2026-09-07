<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A citation attached to a knowledge entity. `notes` is where source
 * inconsistencies are recorded — visibly, never silently resolved (ADR-0004).
 *
 * @mixin Source
 */
class SourceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'publisher' => $this->publisher,
            'url' => $this->url,
            'published_at' => optional($this->published_at)->toDateString(),
            'accessed_at' => optional($this->accessed_at)->toDateString(),
            'reference_period' => $this->reference_period,
            'notes' => $this->notes,
            // Per-attachment note from the `sourceables` pivot (e.g. "page 14"),
            // present only when the source was loaded through a relation.
            'note' => $this->whenPivotLoaded('sourceables', fn () => $this->pivot->note),
        ];
    }
}
