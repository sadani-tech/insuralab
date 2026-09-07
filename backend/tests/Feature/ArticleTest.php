<?php

use App\Enums\ContentStatus;
use App\Enums\ReferenceStatus;
use App\Models\Article;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a general-knowledge article with no provider', function () {
    $article = Article::factory()->create(['provider_id' => null]);

    expect($article->provider_id)->toBeNull()
        ->and($article->isGeneralKnowledge())->toBeTrue()
        ->and($article->isProviderSpecific())->toBeFalse();

    $this->assertDatabaseHas('articles', ['id' => $article->id, 'provider_id' => null]);
});

it('allows a provider-specific article that references a provider', function () {
    $provider = Provider::factory()->create();

    $article = Article::factory()->forProvider($provider)->create();

    expect($article->provider_id)->toBe($provider->id)
        ->and($article->isProviderSpecific())->toBeTrue()
        ->and($article->provider)->toBeInstanceOf(Provider::class)
        ->and($article->provider->is($provider))->toBeTrue();
});

it('scopes general articles separately from provider-specific ones', function () {
    Article::factory()->count(2)->create(['provider_id' => null]);
    Article::factory()->forProvider(Provider::factory()->create())->create();

    expect(Article::general()->count())->toBe(2)
        ->and(Article::whereNotNull('provider_id')->count())->toBe(1);
});

it('blocks publishing a provider-specific article without review metadata', function () {
    $provider = Provider::factory()->create();

    $article = Article::factory()->create([
        'provider_id' => $provider->id,
        'last_reviewed_at' => null,
        'reference_status' => null,
        'status' => ContentStatus::Draft,
    ]);

    $article->status = ContentStatus::Published;

    expect(fn () => $article->save())->toThrow(RuntimeException::class);
});

it('permits publishing a provider-specific article once review metadata is present', function () {
    $provider = Provider::factory()->create();

    $article = Article::factory()->forProvider($provider)->create([
        'last_reviewed_at' => now(),
        'reference_status' => ReferenceStatus::NeedsReview,
    ]);

    $article->update(['status' => ContentStatus::Published]);

    expect($article->fresh()->status)->toBe(ContentStatus::Published)
        ->and($article->fresh()->published_at)->not->toBeNull();
});

it('lets a general article publish freely and stamps published_at', function () {
    $article = Article::factory()->create(['provider_id' => null, 'status' => ContentStatus::Draft]);

    $article->update(['status' => ContentStatus::Published]);

    expect($article->fresh()->published_at)->not->toBeNull();
});
