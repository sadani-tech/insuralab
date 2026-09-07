<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProtectionCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A protection concept (life, health, critical illness, disability, accident,
 * income). These are the spine of the future Protection Needs Assessment —
 * topics to learn about, never product recommendations.
 *
 * @mixin ProtectionCategory
 */
class ProtectionCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            // Present only on the product detail payload, from the pivot.
            'is_primary' => $this->whenPivotLoaded('product_protection_category', fn () => (bool) $this->pivot->is_primary),
        ];
    }
}
