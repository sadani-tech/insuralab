<?php

use App\Enums\ContentStatus;
use App\Enums\InsuranceScheme;
use App\Enums\ReferenceStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProtectionCategory;
use App\Models\Provider;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published products', function () {
    $published = Product::factory()->published()->create();
    $draft = Product::factory()->create(['status' => ContentStatus::Draft]);

    $slugs = collect($this->getJson('/api/v1/products')->assertOk()->json('data'))->pluck('slug');

    expect($slugs)->toContain($published->slug)->not->toContain($draft->slug);
});

it('still lists historical products but exposes their reference_status', function () {
    $historical = Product::factory()->published()->historical()->create();

    $response = $this->getJson('/api/v1/products')->assertOk();

    $row = collect($response->json('data'))->firstWhere('slug', $historical->slug);
    expect($row)->not->toBeNull()
        ->and($row['reference_status'])->toBe('historical');
});

it('filters by provider, product category, scheme and reference status', function () {
    $provider = Provider::factory()->create(['slug' => 'acme']);
    $category = ProductCategory::factory()->create(['slug' => 'critical-illness-rider']);

    $wanted = Product::factory()->published()->create([
        'provider_id' => $provider->id,
        'product_category_id' => $category->id,
        'insurance_scheme' => InsuranceScheme::Sharia,
        'reference_status' => ReferenceStatus::NeedsReview,
    ]);
    Product::factory()->published()->create([
        'insurance_scheme' => InsuranceScheme::Conventional,
        'reference_status' => ReferenceStatus::Current,
    ]);

    expect(collect($this->getJson('/api/v1/products?provider=acme')->json('data'))->pluck('slug'))
        ->toEqual(collect([$wanted->slug]));
    expect(collect($this->getJson('/api/v1/products?category=critical-illness-rider')->json('data'))->pluck('slug'))
        ->toEqual(collect([$wanted->slug]));
    expect(collect($this->getJson('/api/v1/products?insurance_scheme=sharia')->json('data'))->pluck('slug'))
        ->toEqual(collect([$wanted->slug]));
    expect(collect($this->getJson('/api/v1/products?reference_status=needs_review')->json('data'))->pluck('slug'))
        ->toEqual(collect([$wanted->slug]));
});

it('filters by protection category key', function () {
    $ci = ProtectionCategory::factory()->create(['key' => 'critical_illness']);
    $life = ProtectionCategory::factory()->create(['key' => 'life']);

    $ciProduct = Product::factory()->published()->create();
    $ciProduct->protectionCategories()->attach($ci->id, ['is_primary' => true]);
    $lifeProduct = Product::factory()->published()->create();
    $lifeProduct->protectionCategories()->attach($life->id, ['is_primary' => true]);

    expect(collect($this->getJson('/api/v1/products?protection=critical_illness')->json('data'))->pluck('slug'))
        ->toEqual(collect([$ciProduct->slug]));
});

it('returns a product detail with trust metadata, protection categories and sources', function () {
    $product = Product::factory()->published()->create(['reference_period' => 'Training compilation, August 2026']);
    $protection = ProtectionCategory::factory()->create(['key' => 'health', 'name' => 'Health']);
    $product->protectionCategories()->attach($protection->id, ['is_primary' => true]);
    $source = Source::factory()->create(['title' => 'Product Source', 'notes' => 'verify against RIPLAY']);
    $product->sources()->attach($source->id, ['note' => 'p.12']);

    $this->getJson("/api/v1/products/{$product->slug}")
        ->assertOk()
        ->assertJsonPath('data.trust.reference_status', $product->reference_status->value)
        ->assertJsonPath('data.trust.reference_period', 'Training compilation, August 2026')
        ->assertJsonPath('data.protection_categories.0.key', 'health')
        ->assertJsonPath('data.protection_categories.0.is_primary', true)
        ->assertJsonPath('data.sources.0.title', 'Product Source')
        ->assertJsonPath('data.sources.0.notes', 'verify against RIPLAY');
});

it('only exposes allow-listed metadata keys', function () {
    $product = Product::factory()->published()->create([
        'metadata' => [
            'payout_model' => 'lump_sum',
            'is_paydi' => true,
            'internal_note' => 'DO NOT SHOW',
            'kind' => 'compensation_reference',
        ],
    ]);

    $response = $this->getJson("/api/v1/products/{$product->slug}")->assertOk();

    // Note: Postgres jsonb does not preserve key order, so assert by key/value.
    $metadata = $response->json('data.metadata');
    expect($metadata)->toHaveKeys(['payout_model', 'is_paydi'])
        ->and($metadata)->not->toHaveKeys(['internal_note', 'kind'])
        ->and($metadata['payout_model'])->toBe('lump_sum')
        ->and($metadata['is_paydi'])->toBeTrue();
    expect($response->getContent())->not->toContain('DO NOT SHOW');
});

it('links a product to general learning concepts and glossary terms (concept-first)', function () {
    $product = Product::factory()->published()->create();
    $concept = \App\Models\Article::factory()->published()->create(['title' => 'Apa Itu Penyakit Kritis']);
    $draftConcept = \App\Models\Article::factory()->create(['status' => ContentStatus::Draft]);
    $term = \App\Models\GlossaryTerm::factory()->create(['term' => 'Lump Sum', 'slug' => 'lump-sum']);
    $product->relatedArticles()->attach([$concept->id, $draftConcept->id]);
    $product->glossaryTerms()->attach($term->id);

    $response = $this->getJson("/api/v1/products/{$product->slug}")->assertOk();

    expect(collect($response->json('data.related_articles'))->pluck('slug'))
        ->toContain($concept->slug)->not->toContain($draftConcept->slug);
    expect($response->json('data.related_terms.0.slug'))->toBe('lump-sum');
});

it('404s for a draft or unknown product', function () {
    $draft = Product::factory()->create(['status' => ContentStatus::Draft]);

    $this->getJson("/api/v1/products/{$draft->slug}")->assertNotFound();
    $this->getJson('/api/v1/products/ghost')->assertNotFound();
});

it('rejects an invalid reference_status filter', function () {
    $this->getJson('/api/v1/products?reference_status=maybe')
        ->assertStatus(422)
        ->assertJsonValidationErrors('reference_status');
});
