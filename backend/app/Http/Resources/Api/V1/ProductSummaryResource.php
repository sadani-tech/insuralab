<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product shape for list endpoints. Educational records only — never "buy this".
 *
 * @mixin Product
 */
class ProductSummaryResource extends JsonResource
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
            'short_description' => $this->short_description,
            'insurance_scheme' => $this->insurance_scheme?->value,
            'product_structure' => $this->product_structure?->value,
            'provider' => new ProviderSummaryResource($this->whenLoaded('provider')),
            'product_category' => new ProductCategoryResource($this->whenLoaded('productCategory')),
            'protection_categories' => ProtectionCategoryResource::collection($this->whenLoaded('protectionCategories')),
            'reference_status' => $this->reference_status?->value,
            'reference_period' => $this->reference_period,
            'last_reviewed_at' => optional($this->last_reviewed_at)->toDateString(),
        ];
    }
}
