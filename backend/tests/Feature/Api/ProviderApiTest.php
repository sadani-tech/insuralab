<?php

use App\Models\Article;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only active providers with summary counts', function () {
    $active = Provider::factory()->create(['name' => 'Acme Assurance']);
    $inactive = Provider::factory()->inactive()->create();
    Product::factory()->count(2)->published()->create(['provider_id' => $active->id]);
    Product::factory()->create(['provider_id' => $active->id]); // draft — not counted
    Article::factory()->forProvider($active)->published()->create();

    $response = $this->getJson('/api/v1/providers')->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'products_count', 'articles_count']], 'meta']);

    $slugs = collect($response->json('data'))->pluck('slug');
    expect($slugs)->toContain($active->slug)->not->toContain($inactive->slug);

    $row = collect($response->json('data'))->firstWhere('slug', $active->slug);
    expect($row['products_count'])->toBe(2)->and($row['articles_count'])->toBe(1);
});

it('returns a provider profile with counts, its provider articles, and sources — but no nested product list', function () {
    $provider = Provider::factory()->create(['slug' => 'acme']);
    Product::factory()->count(3)->published()->create(['provider_id' => $provider->id]);
    $providerArticle = Article::factory()->forProvider($provider)->published()->create();
    Article::factory()->forProvider($provider)->create(['status' => \App\Enums\ContentStatus::Draft]);
    $source = Source::factory()->create(['title' => 'Provider Training Source']);
    $provider->sources()->attach($source->id, ['note' => 'primary']);

    $response = $this->getJson('/api/v1/providers/acme')
        ->assertOk()
        ->assertJsonPath('data.slug', 'acme')
        ->assertJsonPath('data.counts.products', 3)
        ->assertJsonPath('data.sources.0.title', 'Provider Training Source');

    // Products are fetched from /products?provider=… , never nested here.
    expect($response->json('data'))->not->toHaveKey('products');
    // The few provider-specific articles ARE included (published only).
    expect(collect($response->json('data.articles'))->pluck('slug'))
        ->toEqual(collect([$providerArticle->slug]));
});

it('exposes only allow-listed provider metadata keys', function () {
    $provider = Provider::factory()->create([
        'slug' => 'acme',
        'metadata' => ['entities' => [['name' => 'Acme Life']], 'internal_ref' => 'DO NOT SHOW'],
    ]);

    $response = $this->getJson('/api/v1/providers/acme')->assertOk();

    expect($response->json('data.metadata'))->toHaveKey('entities')
        ->and($response->json('data.metadata'))->not->toHaveKey('internal_ref');
    expect($response->getContent())->not->toContain('DO NOT SHOW');
});

it('404s for a missing or inactive provider', function () {
    $inactive = Provider::factory()->inactive()->create(['slug' => 'hidden-co']);

    $this->getJson('/api/v1/providers/nobody')->assertNotFound();
    $this->getJson('/api/v1/providers/hidden-co')->assertNotFound();
});
