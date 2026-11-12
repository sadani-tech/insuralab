<?php

namespace App\Models;

use App\Enums\ReferenceStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSources;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GlossaryTerm extends Model
{
    use HasFactory;
    use HasSlug;
    use HasSources;

    protected $slugSourceColumn = 'term';

    protected $fillable = [
        'provider_id', 'term', 'slug', 'short_definition', 'definition',
        'category', 'metadata', 'last_reviewed_at', 'reference_status', 'explorer_published',
    ];

    protected $casts = [
        'metadata' => 'array',
        'explorer_published' => 'boolean',
        'last_reviewed_at' => 'date',
        'reference_status' => ReferenceStatus::class,
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function relatedTerms(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'glossary_term_related',
            'glossary_term_id',
            'related_term_id',
        );
    }

    /** Learning articles that teach this term (explicit links only). */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'glossary_term_article');
    }
}
