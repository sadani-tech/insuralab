<?php

namespace App\Http\Requests\Api\V1\Concerns;

/**
 * Shared pagination query rules for public list endpoints. A hard `per_page`
 * ceiling stops a caller asking for an unbounded page.
 *
 * (Constants live on {@see Pagination} because PHP 8.1 traits cannot declare
 * constants.)
 */
trait PaginatesResults
{
    /**
     * @return array<string, mixed>
     */
    protected function paginationRules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.Pagination::MAX_PER_PAGE],
        ];
    }

    public function perPage(): int
    {
        $value = (int) $this->validated('per_page', Pagination::DEFAULT_PER_PAGE);

        return max(1, min($value, Pagination::MAX_PER_PAGE));
    }
}
