<?php

use App\Models\GlossaryTerm;
use App\Models\KnowledgeEntry;
use App\Models\KnowledgeRelation;
use App\Models\Source;
use App\Models\SourcePassage;
use App\Models\User;
use Database\Seeders\KnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    $this->seed(KnowledgeSeeder::class);
});

it('serves a complete bounded library with no AI service or credentials', function () {
    $response = $this->getJson('/api/v1/knowledge')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    expect(count($response->json('data.nodes')))->toBe(49)
        ->and(count($response->json('data.edges')))->toBe(87)
        ->and(count($response->json('data.entries')))->toBe(22)
        ->and($response->json('data.ai.available'))->toBeFalse();
    Http::assertNothingSent();
});

it('revokes source access immediately across nodes relations entries and passages', function () {
    Source::query()->update(['access_level' => 'restricted']);
    $response = $this->getJson('/api/v1/knowledge')->assertOk();
    foreach (['nodes', 'edges', 'entries', 'passages'] as $field) {
        expect($response->json('data.'.$field))->toBe([]);
    }
});

it('does not expose draft entries or unpublished evidence and handles unknown searches honestly', function () {
    $entry = KnowledgeEntry::where('kind', 'faq')->first();
    $entry->update(['published' => false]);
    $this->getJson('/api/v1/knowledge?q='.urlencode($entry->title))->assertOk()->assertJsonCount(0, 'data.entries');
    $this->getJson('/api/v1/knowledge?q=zzzx-nonexistent')->assertJsonCount(0, 'data.entries');
    SourcePassage::query()->update(['published' => false]);
    $this->getJson('/api/v1/knowledge')->assertJsonCount(0, 'data.nodes')->assertJsonCount(0, 'data.entries')->assertJsonCount(0, 'data.edges')->assertJsonCount(0, 'data.passages');
});

it('matches persisted FAQ content and rejects excessive or malformed queries', function () {
    $result = $this->getJson('/api/v1/knowledge?q=cashless')->assertOk();
    expect(collect($result->json('data.entries'))->where('kind', 'faq')->count())->toBeGreaterThan(0);
    $this->getJson('/api/v1/knowledge?q='.str_repeat('x', 161))->assertUnprocessable();
    $this->getJson('/api/v1/knowledge?q[]=x')->assertUnprocessable();
    $this->getJson('/api/v1/knowledge?q=%25')->assertOk()->assertJsonCount(0, 'data.entries');
});

it('keeps imports idempotent without republishing withdrawn content', function () {
    $node = GlossaryTerm::first();
    $node->update(['explorer_published' => false]);
    $entry = KnowledgeEntry::first();
    $entry->update(['published' => false, 'title' => 'Edited by an editor']);
    $this->seed(KnowledgeSeeder::class);
    expect(GlossaryTerm::count())->toBe(45)
        ->and(KnowledgeRelation::count())->toBe(85)
        ->and(KnowledgeEntry::count())->toBe(22)
        ->and($node->fresh()->explorer_published)->toBeFalse()
        ->and($entry->fresh()->title)->toBe('Edited by an editor')
        ->and($entry->fresh()->published)->toBeFalse();
});

it('defaults new source rights to unreviewed and hides them in legacy public resources', function () {
    $source = Source::create(['title' => 'DO NOT EXPOSE INTERNAL SOURCE']);
    expect($source->fresh()->access_level)->toBe('unreviewed');
    $term = GlossaryTerm::first();
    $term->sources()->attach($source);
    $this->getJson('/api/v1/glossary/'.$term->slug)->assertNotFound()->assertDontSee('DO NOT EXPOSE INTERNAL SOURCE');
});

it('denies the inactive operational endpoint even to signed in users and admins', function () {
    $this->getJson('/api/v1/agent/workspace')->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson('/api/v1/agent/workspace')->assertForbidden();
    $this->actingAs(User::factory()->create(['is_admin' => true]))->getJson('/api/v1/agent/workspace')->assertForbidden();
});

it('never returns unsafe source links', function () {
    Source::query()->update(['url' => 'javascript:alert(1)']);
    $this->getJson('/api/v1/knowledge')->assertOk()->assertJsonPath('data.passages.0.url', null);
});

it('keeps hidden terms out of related-entry metadata and caps expanded corpora', function () {
    $hidden = GlossaryTerm::first();
    $hidden->update(['explorer_published' => false]);
    $response = $this->getJson('/api/v1/knowledge')->assertOk();
    expect(collect($response->json('data.entries'))->flatMap(fn ($e) => $e['terms'])->contains($hidden->slug))->toBeFalse();
    $source = Source::where('access_level', 'public')->first();
    GlossaryTerm::factory()->count(130)->create(['explorer_published' => true])->each(fn ($t) => $t->sources()->attach($source));
    $response = $this->getJson('/api/v1/knowledge')->assertOk();
    expect(count($response->json('data.nodes')))->toBeLessThanOrEqual(120)
        ->and(count($response->json('data.edges')))->toBeLessThanOrEqual(240);
});

it('returns editorial matches without AI even when asked to use an unconfigured provider', function () {
    $this->postJson('/api/v1/knowledge/assist', ['question' => 'Jelaskan cashless', 'use_ai' => true])
        ->assertOk()->assertJsonPath('data.mode', 'curated')->assertJsonPath('data.explanation', null);
    Http::assertNothingSent();
});

it('keeps curated results when an optional provider times out or invents a citation', function () {
    config(['knowledge.ai_enabled' => true]);
    $provider = Mockery::mock(\App\Contracts\KnowledgeExplainer::class);
    $provider->shouldReceive('available')->andReturn(true);
    $provider->shouldReceive('explain')->once()->andThrow(new RuntimeException('timeout'));
    $provider->shouldReceive('explain')->once()->andReturn(['text' => 'Unverified answer', 'passage_ids' => [999999]]);
    $this->app->instance(\App\Contracts\KnowledgeExplainer::class, $provider);
    for ($i = 0; $i < 2; $i++) {
        $response = $this->postJson('/api/v1/knowledge/assist', ['question' => 'cashless', 'use_ai' => true])
            ->assertOk()->assertJsonPath('data.mode', 'curated')->assertJsonPath('data.explanation', null);
        expect(count($response->json('data.entries')))->toBeGreaterThan(0);
    }
});

it('does not retrieve or send restricted passages to an optional provider', function () {
    config(['knowledge.ai_enabled' => true]);
    Source::query()->update(['access_level' => 'restricted']);
    $provider = Mockery::mock(\App\Contracts\KnowledgeExplainer::class);
    $provider->shouldReceive('available')->andReturn(true);
    $provider->shouldNotReceive('explain');
    $this->app->instance(\App\Contracts\KnowledgeExplainer::class, $provider);
    $this->postJson('/api/v1/knowledge/assist', ['question' => 'cashless', 'use_ai' => true])
        ->assertOk()->assertJsonCount(0, 'data.entries')->assertJsonCount(0, 'data.passages');
});

it('keeps cited passages immutable while allowing publication to be revoked', function () {
    $passage = SourcePassage::first();
    expect(fn () => $passage->update(['body' => 'Changed evidence']))->toThrow(\Illuminate\Validation\ValidationException::class);
    $passage->refresh()->update(['published' => false]);
    expect($passage->fresh()->published)->toBeFalse();
});
