<?php

use App\Models\Article;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('caps per_page at 100', function () {
    Article::factory()->count(3)->published()->create();

    $this->getJson('/api/v1/articles?per_page=100')->assertOk()->assertJsonPath('meta.per_page', 100);
    $this->getJson('/api/v1/articles?per_page=101')->assertStatus(422)->assertJsonValidationErrors('per_page');
    $this->getJson('/api/v1/articles?per_page=0')->assertStatus(422)->assertJsonValidationErrors('per_page');
});

it('returns JSON 404 for an unknown route even without an Accept header', function () {
    $this->get('/api/v1/nonexistent-endpoint')
        ->assertNotFound()
        ->assertHeader('content-type', 'application/json')
        ->assertJson(['message' => 'Resource not found.']);
});

it('serves the real seeded dataset over the API without N+1 explosions', function () {
    $this->seed(DatabaseSeeder::class);

    \DB::enableQueryLog();
    $response = $this->getJson('/api/v1/articles?per_page=50')->assertOk();
    $queryCount = count(\DB::getQueryLog());
    \DB::disableQueryLog();

    // All seeded articles are published. A constant handful of queries (list +
    // eager loads + pagination count), not one-per-row.
    expect($response->json('meta.total'))->toBeGreaterThanOrEqual(25);
    expect($queryCount)->toBeLessThan(10);
});

it('keeps training-derived provider content private until source rights are approved', function () {
    $this->seed(DatabaseSeeder::class);

    $this->getJson('/api/v1/articles/bagaimana-allianz-flexi-ci-dijelaskan-dalam-materi-pelatihan')
        ->assertNotFound();
});

it('requires public source rights even for historical product records', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->getJson('/api/v1/products?reference_status=historical')->assertOk();

    expect(collect($response->json('data'))->pluck('reference_status')->unique()->all())
        ->toBe([]);
});
