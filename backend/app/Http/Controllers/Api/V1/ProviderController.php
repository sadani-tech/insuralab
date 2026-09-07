<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProviderIndexRequest;
use App\Http\Resources\Api\V1\ProviderResource;
use App\Http\Resources\Api\V1\ProviderSummaryResource;
use App\Models\Provider;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProviderController extends Controller
{
    public function index(ProviderIndexRequest $request): AnonymousResourceCollection
    {
        $providers = Provider::query()
            ->public()
            ->withCount([
                'products as products_count' => fn ($q) => $q->published(),
                'articles as articles_count' => fn ($q) => $q->public(),
            ])
            ->orderBy('name')
            ->paginate($request->perPage())
            ->withQueryString();

        return ProviderSummaryResource::collection($providers);
    }

    public function show(string $slug): ProviderResource
    {
        $provider = Provider::query()
            ->public()
            ->withCount([
                'products as products_count' => fn ($q) => $q->published(),
                'articles as articles_count' => fn ($q) => $q->public(),
            ])
            ->with([
                'sources',
                'articles' => fn ($q) => $q->public()
                    ->with('category:id,name,slug', 'provider:id,name,slug,country_of_origin,last_reviewed_at')
                    ->orderBy('level')
                    ->orderBy('title'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProviderResource($provider);
    }
}
