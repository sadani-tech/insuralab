<?php

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, past-dated articles', function () {
    $published = Article::factory()->published()->create();
    $draft = Article::factory()->create(['status' => ContentStatus::Draft]);
    $archived = Article::factory()->create(['status' => ContentStatus::Archived]);
    $future = Article::factory()->published()->create(['published_at' => now()->addWeek()]);

    $response = $this->getJson('/api/v1/articles');

    $response->assertOk();
    $slugs = collect($response->json('data'))->pluck('slug');

    expect($slugs)->toContain($published->slug)
        ->not->toContain($draft->slug)
        ->not->toContain($archived->slug)
        ->not->toContain($future->slug);
});

it('returns the documented collection envelope', function () {
    Article::factory()->count(3)->published()->create();

    $this->getJson('/api/v1/articles?per_page=2')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'title', 'slug', 'excerpt', 'level', 'knowledge_type', 'reference_status']],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonCount(2, 'data');
});

it('retrieves an article by slug with body and trust metadata', function () {
    $article = Article::factory()->published()->create();

    $this->getJson("/api/v1/articles/{$article->slug}")
        ->assertOk()
        ->assertJsonPath('data.slug', $article->slug)
        ->assertJsonPath('data.knowledge_type', 'general')
        ->assertJsonStructure(['data' => ['body', 'tags', 'trust' => ['reference_status', 'last_reviewed_at'], 'sources']]);
});

it('returns 404 for an unknown or non-public slug', function () {
    $draft = Article::factory()->create(['status' => ContentStatus::Draft]);

    $this->getJson('/api/v1/articles/nope-not-here')->assertNotFound()
        ->assertJson(['message' => 'Resource not found.']);
    $this->getJson("/api/v1/articles/{$draft->slug}")->assertNotFound();
});

it('never exposes a draft body even if the slug is guessed', function () {
    $draft = Article::factory()->create([
        'status' => ContentStatus::Draft,
        'body' => 'SECRET DRAFT BODY',
    ]);

    $response = $this->getJson("/api/v1/articles/{$draft->slug}");

    $response->assertNotFound();
    expect($response->getContent())->not->toContain('SECRET DRAFT BODY');
});

it('filters by category slug', function () {
    $fundamentals = Category::factory()->create(['slug' => 'fundamentals']);
    $other = Category::factory()->create(['slug' => 'sharia']);
    $wanted = Article::factory()->published()->create(['category_id' => $fundamentals->id]);
    Article::factory()->published()->create(['category_id' => $other->id]);

    $data = $this->getJson('/api/v1/articles?category=fundamentals')->assertOk()->json('data');

    expect(collect($data)->pluck('slug'))->toEqual(collect([$wanted->slug]));
});

it('filters by level', function () {
    Article::factory()->published()->create(['level' => ContentLevel::Beginner]);
    $advanced = Article::factory()->published()->create(['level' => ContentLevel::Advanced]);

    $data = $this->getJson('/api/v1/articles?level=advanced')->assertOk()->json('data');

    expect(collect($data)->pluck('slug'))->toEqual(collect([$advanced->slug]));
});

it('filters by provider slug and by knowledge_type', function () {
    $provider = Provider::factory()->create(['slug' => 'allianz']);
    $providerArticle = Article::factory()->forProvider($provider)->published()->create();
    $generalArticle = Article::factory()->published()->create();

    $byProvider = $this->getJson('/api/v1/articles?provider=allianz')->assertOk()->json('data');
    expect(collect($byProvider)->pluck('slug'))->toEqual(collect([$providerArticle->slug]));

    $general = $this->getJson('/api/v1/articles?knowledge_type=general')->assertOk()->json('data');
    expect(collect($general)->pluck('slug'))->toContain($generalArticle->slug)
        ->not->toContain($providerArticle->slug);
});

it('marks a general article provider as null and a provider article provider as present', function () {
    $general = Article::factory()->published()->create();
    $provider = Provider::factory()->create();
    $specific = Article::factory()->forProvider($provider)->published()->create();

    $this->getJson("/api/v1/articles/{$general->slug}")
        ->assertJsonPath('data.provider', null)
        ->assertJsonPath('data.knowledge_type', 'general')
        ->assertJsonPath('data.trust.is_provider_specific', false);

    $this->getJson("/api/v1/articles/{$specific->slug}")
        ->assertJsonPath('data.provider.slug', $provider->slug)
        ->assertJsonPath('data.knowledge_type', 'provider_specific')
        ->assertJsonPath('data.trust.is_provider_specific', true)
        ->assertJsonPath('data.trust.reference_status', 'needs_review');
});

it('serialises attached sources including the pivot note', function () {
    $article = Article::factory()->published()->create();
    $source = Source::factory()->create(['title' => 'Training Deck', 'notes' => 'inconsistency recorded here']);
    $article->sources()->attach($source->id, ['note' => 'page 14']);

    $this->getJson("/api/v1/articles/{$article->slug}")
        ->assertOk()
        ->assertJsonPath('data.sources.0.title', 'Training Deck')
        ->assertJsonPath('data.sources.0.notes', 'inconsistency recorded here')
        ->assertJsonPath('data.sources.0.note', 'page 14');
});

it('paginates', function () {
    Article::factory()->count(25)->published()->create();

    $page1 = $this->getJson('/api/v1/articles?per_page=10')->assertOk();
    $page1->assertJsonCount(10, 'data')->assertJsonPath('meta.total', 25)->assertJsonPath('meta.last_page', 3);

    $this->getJson('/api/v1/articles?per_page=10&page=3')->assertOk()->assertJsonCount(5, 'data');
});

it('rejects an invalid level and an oversized per_page', function () {
    $this->getJson('/api/v1/articles?level=wizard')->assertStatus(422)->assertJsonValidationErrors('level');
    $this->getJson('/api/v1/articles?per_page=101')->assertStatus(422)->assertJsonValidationErrors('per_page');
    $this->getJson('/api/v1/articles?category=ghost-category')->assertStatus(422)->assertJsonValidationErrors('category');
});
