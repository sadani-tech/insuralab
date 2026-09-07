<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Api\V1\Concerns\PaginatesResults;
use Illuminate\Foundation\Http\FormRequest;

class GlossaryIndexRequest extends FormRequest
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
            'letter' => ['sometimes', 'string', 'alpha', 'size:1'],
            'category' => ['sometimes', 'string', 'max:50'],
            'q' => ['sometimes', 'string', 'max:100'],
        ]);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('letter')) {
            $this->merge(['letter' => mb_strtoupper((string) $this->query('letter'))]);
        }
    }
}
