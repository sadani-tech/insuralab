<?php

namespace App\Models;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Enums\ReferenceStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSources;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasFactory;
    use HasSlug;
    use HasSources;

    protected $slugSourceColumn = 'title';

    protected $fillable = [
        'category_id', 'provider_id',
        'title', 'slug', 'excerpt', 'body', 'key_takeaways',
        'level', 'status', 'is_featured', 'estimated_reading_minutes',
        'published_at', 'last_reviewed_at', 'reference_period', 'reference_status',
    ];

    protected $casts = [
        'level' => ContentLevel::class,
        'status' => ContentStatus::class,
        'reference_status' => ReferenceStatus::class,
        'is_featured' => 'boolean',
        'estimated_reading_minutes' => 'integer',
        'published_at' => 'datetime',
        'last_reviewed_at' => 'date',
        'key_takeaways' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            // Editorial guardrail (ADR-0004 / CONTENT_GUIDE §5): a provider-specific
            // article may not be published without review metadata.
            if ($article->isProviderSpecific()
                && $article->status === ContentStatus::Published
                && ! $article->hasReviewMetadata()) {
                throw new \RuntimeException(
                    'Provider-specific articles require a last reviewed date and a reference status before they can be published.'
                );
            }

            if ($article->status === ContentStatus::Published && $article->published_at === null) {
                $article->published_at = now();
            }
        });
    }

    public function isGeneralKnowledge(): bool
    {
        return $this->provider_id === null;
    }

    public function isProviderSpecific(): bool
    {
        return $this->provider_id !== null;
    }

    public function hasReviewMetadata(): bool
    {
        return $this->last_reviewed_at !== null && $this->reference_status !== null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value);
    }

    /**
     * Content safe to expose over the public API: published, and past its
     * publication timestamp. Future-dated articles stay hidden until due.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeGeneral(Builder $query): Builder
    {
        return $query->whereNull('provider_id');
    }

    public function scopeForProvider(Builder $query, int $providerId): Builder
    {
        return $query->where('provider_id', $providerId);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    /** Glossary terms explicitly linked to this article (curated, never auto-parsed). */
    public function glossaryTerms(): BelongsToMany
    {
        return $this->belongsToMany(GlossaryTerm::class, 'glossary_term_article');
    }

    public function learningPaths(): BelongsToMany
    {
        return $this->belongsToMany(LearningPath::class, 'learning_path_items')
            ->withPivot(['note', 'position'])
            ->withTimestamps();
    }

    /**
     * Deterministic "Pelajari Selanjutnya" — same category first, then shared
     * tags, same knowledge type, never random, never an ML model.
     *
     * @return \Illuminate\Support\Collection<int, Article>
     */
    public function relatedArticles(int $limit = 4): \Illuminate\Support\Collection
    {
        $tagIds = $this->tags->pluck('id');

        return static::query()
            ->public()
            ->where('id', '!=', $this->id)
            ->where('provider_id', $this->provider_id)
            ->with('category:id,name,slug', 'provider:id,name,slug,country_of_origin,last_reviewed_at')
            ->withCount(['tags as shared_tags' => fn ($q) => $q->whereIn('tags.id', $tagIds)])
            ->orderByRaw('case when category_id = ? then 0 else 1 end', [$this->category_id])
            ->orderByDesc('shared_tags')
            ->orderByRaw('case when level = ? then 0 else 1 end', [$this->level?->value])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }
}
