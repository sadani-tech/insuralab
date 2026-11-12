<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GlossaryIndexRequest;
use App\Http\Resources\Api\V1\GlossaryTermResource;
use App\Http\Resources\Api\V1\GlossaryTermSummaryResource;
use App\Models\GlossaryTerm;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GlossaryTermController extends Controller
{
    public function index(GlossaryIndexRequest $request): AnonymousResourceCollection
    {
        $terms = GlossaryTerm::query()->withPublicEvidence()
            ->withCount(['articles as articles_count' => fn ($q) => $q->public()])
            ->when($request->filled('letter'), fn ($q) => $q->where('term', 'ilike', $request->string('letter').'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($inner) => $inner
                    ->where('term', 'ilike', $term)
                    ->orWhere('short_definition', 'ilike', $term)
                    ->orWhere('definition', 'ilike', $term));
            })
            ->orderBy('term')
            ->paginate($request->perPage())
            ->withQueryString();

        return GlossaryTermSummaryResource::collection($terms);
    }

    public function show(string $slug): GlossaryTermResource
    {
        $term = GlossaryTerm::query()->withPublicEvidence()
            ->with([
                'provider:id,name,slug,country_of_origin,last_reviewed_at',
                'relatedTerms' => fn ($q) => $q->withPublicEvidence()->select('glossary_terms.id', 'term', 'slug', 'short_definition'),
                'articles' => fn ($q) => $q->public()
                    ->with('category:id,name,slug', 'provider:id,name,slug,country_of_origin,last_reviewed_at')
                    ->orderByDesc('is_featured')
                    ->orderBy('title'),
                'sources',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new GlossaryTermResource($term);
    }
}
