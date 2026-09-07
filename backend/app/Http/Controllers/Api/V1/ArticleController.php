<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ArticleIndexRequest;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Resources\Api\V1\ArticleSummaryResource;
use App\Models\Article;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    /**
     * Published general knowledge + provider-specific educational articles.
     * Drafts, archived, and future-dated articles are never returned.
     */
    public function index(ArticleIndexRequest $request): AnonymousResourceCollection
    {
        $articles = Article::query()
            ->public()
            ->with(['category:id,name,slug', 'provider:id,name,slug,country_of_origin,last_reviewed_at'])
            ->when($request->filled('category'), fn ($q) => $q->whereHas(
                'category',
                fn ($c) => $c->where('slug', $request->string('category'))
            ))
            ->when($request->filled('provider'), fn ($q) => $q->whereHas(
                'provider',
                fn ($p) => $p->where('slug', $request->string('provider'))
            ))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->string('level')))
            ->when($request->has('featured'), fn ($q) => $q->where('is_featured', $request->boolean('featured')))
            ->when($request->input('knowledge_type') === 'general', fn ($q) => $q->whereNull('provider_id'))
            ->when($request->input('knowledge_type') === 'provider_specific', fn ($q) => $q->whereNotNull('provider_id'))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate($request->perPage())
            ->withQueryString();

        return ArticleSummaryResource::collection($articles);
    }

    public function show(string $slug): ArticleResource
    {
        $article = Article::query()
            ->public()
            ->with([
                'category:id,name,slug',
                'provider:id,name,slug,country_of_origin,last_reviewed_at',
                'tags:id,name,slug',
                'glossaryTerms:id,term,slug,short_definition',
                'sources',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // Deterministic related articles (same category / shared tags / same
        // knowledge type) — attached as a relation so the resource can guard it.
        $article->setRelation('relatedArticles', $article->relatedArticles());

        return new ArticleResource($article);
    }
}
