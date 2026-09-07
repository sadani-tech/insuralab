<?php

namespace App\Http\Requests\Api\V1\Concerns;

/**
 * Pagination limits for the public API list endpoints.
 */
final class Pagination
{
    public const DEFAULT_PER_PAGE = 15;

    public const MAX_PER_PAGE = 100;
}
