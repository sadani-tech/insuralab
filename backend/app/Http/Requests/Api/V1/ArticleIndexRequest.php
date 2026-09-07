<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ContentLevel;
use App\Http\Requests\Api\V1\Concerns\PaginatesResults;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ArticleIndexRequest extends FormRequest
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
            'category' => ['sometimes', 'string', 'exists:categories,slug'],
            'provider' => ['sometimes', 'string', 'exists:providers,slug'],
            'level' => ['sometimes', new Enum(ContentLevel::class)],
            'featured' => ['sometimes', 'boolean'],
            'knowledge_type' => ['sometimes', Rule::in(['general', 'provider_specific'])],
        ]);
    }
}
