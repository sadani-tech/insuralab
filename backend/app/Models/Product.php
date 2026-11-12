<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\InsuranceScheme;
use App\Enums\ProductStructure;
use App\Enums\ReferenceStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSources;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    use HasSlug;
    use HasSources;

    protected $fillable = [
        'provider_id', 'product_category_id',
        'name', 'slug', 'short_description', 'description',
        'insurance_scheme', 'product_structure',
        'reference_status', 'reference_period', 'last_reviewed_at',
        'status', 'metadata',
    ];

    protected $casts = [
        'insurance_scheme' => InsuranceScheme::class,
        'product_structure' => ProductStructure::class,
        'reference_status' => ReferenceStatus::class,
        'status' => ContentStatus::class,
        'last_reviewed_at' => 'date',
        'metadata' => 'array',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->withPublicEvidence()->where('status', ContentStatus::Published->value);
    }

    /**
     * Content safe to expose over the public API. `historical` products are still
     * returned — they are deliberate educational history — but the payload always
     * carries `reference_status` so the frontend can badge them (ADR-0004).
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->published();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function protectionCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProtectionCategory::class, 'product_protection_category')
            ->withPivot('is_primary');
    }

    /** General learning concepts a reader should understand before this product. */
    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'product_article');
    }

    public function glossaryTerms(): BelongsToMany
    {
        return $this->belongsToMany(GlossaryTerm::class, 'product_glossary_term');
    }
}
