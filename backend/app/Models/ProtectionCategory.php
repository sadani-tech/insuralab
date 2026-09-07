<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProtectionCategory extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'name', 'slug', 'key', 'short_description', 'description', 'icon', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_protection_category')
            ->withPivot('is_primary');
    }
}
