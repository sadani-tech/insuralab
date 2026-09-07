<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\InsuranceScheme;
use App\Enums\ProductStructure;
use App\Enums\ReferenceStatus;
use App\Http\Requests\Api\V1\Concerns\PaginatesResults;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ProductIndexRequest extends FormRequest
{
    use PaginatesResults;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->paginationRules(), [
            'provider' => ['sometimes', 'string', 'exists:providers,slug'],
            'category' => ['sometimes', 'string', 'exists:product_categories,slug'],
            'insurance_scheme' => ['sometimes', new Enum(InsuranceScheme::class)],
            'product_structure' => ['sometimes', new Enum(ProductStructure::class)],
            'reference_status' => ['sometimes', new Enum(ReferenceStatus::class)],
            'protection' => ['sometimes', 'string', 'exists:protection_categories,key'],
        ]);
    }
}
