<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProductIndexRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProductSummaryResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Published educational product records. `historical` products still appear —
     * they are deliberate insurance history — but every payload carries
     * `reference_status` so the frontend can badge them (ADR-0004).
     */
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->published()
            ->with([
                'provider:id,name,slug,country_of_origin,last_reviewed_at',
                'productCategory',
                'protectionCategories',
            ])
            ->when($request->filled('provider'), fn ($q) => $q->whereHas(
                'provider',
                fn ($p) => $p->where('slug', $request->string('provider'))
            ))
            ->when($request->filled('category'), fn ($q) => $q->whereHas(
                'productCategory',
                fn ($c) => $c->where('slug', $request->string('category'))
            ))
            ->when($request->filled('protection'), fn ($q) => $q->whereHas(
                'protectionCategories',
                fn ($p) => $p->where('key', $request->string('protection'))
            ))
            ->when($request->filled('insurance_scheme'), fn ($q) => $q->where('insurance_scheme', $request->string('insurance_scheme')))
            ->when($request->filled('product_structure'), fn ($q) => $q->where('product_structure', $request->string('product_structure')))
            ->when($request->filled('reference_status'), fn ($q) => $q->where('reference_status', $request->string('reference_status')))
            ->orderBy('name')
            ->paginate($request->perPage())
            ->withQueryString();

        return ProductSummaryResource::collection($products);
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->published()
            ->with([
                'provider:id,name,slug,country_of_origin,last_reviewed_at',
                'productCategory',
                'protectionCategories',
                'sources',
                'glossaryTerms' => fn ($q) => $q->withPublicEvidence()->select('glossary_terms.id', 'term', 'slug', 'short_definition'),
                'relatedArticles' => fn ($q) => $q->public()
                    ->with('category:id,name,slug', 'provider:id,name,slug,country_of_origin,last_reviewed_at')
                    ->orderBy('title'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
