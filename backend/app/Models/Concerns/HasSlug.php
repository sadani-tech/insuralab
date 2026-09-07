<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Auto-populates a model's `slug` from its `slugSourceColumn` (default `name`)
 * on create when a slug was not supplied.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $sourceColumn = property_exists($model, 'slugSourceColumn')
                ? $model->slugSourceColumn
                : 'name';

            if (blank($model->slug) && filled($model->{$sourceColumn})) {
                $model->slug = Str::slug($model->{$sourceColumn});
            }
        });
    }
}
