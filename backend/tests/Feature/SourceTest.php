<?php

use App\Models\Article;
use App\Models\GlossaryTerm;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('attaches one reusable source to many kinds of entity polymorphically', function () {
    $source = Source::factory()->create(['title' => 'Shared training reference']);

    $article = Article::factory()->create(['provider_id' => null]);
    $provider = Provider::factory()->create();
    $product = Product::factory()->create();
    $term = GlossaryTerm::factory()->create();

    $article->sources()->attach($source->id, ['note' => 'page 12']);
    $provider->sources()->attach($source->id);
    $product->sources()->attach($source->id);
    $term->sources()->attach($source->id);

    expect($article->sources)->toHaveCount(1)
        ->and($article->sources->first()->pivot->note)->toBe('page 12')
        ->and($provider->sources)->toHaveCount(1)
        ->and($product->sources)->toHaveCount(1)
        ->and($term->sources)->toHaveCount(1);

    $this->assertDatabaseCount('sourceables', 4);
});

it('resolves the inverse relations from the source side', function () {
    $source = Source::factory()->create();
    $source->articles()->attach(Article::factory()->create(['provider_id' => null]));
    $source->providers()->attach(Provider::factory()->create());
    $source->products()->attach(Product::factory()->create());

    expect($source->articles)->toHaveCount(1)
        ->and($source->providers)->toHaveCount(1)
        ->and($source->products)->toHaveCount(1);
});

it('stores source notes for recording inconsistencies visibly', function () {
    $source = Source::factory()->create([
        'notes' => 'Option 3: text says 2 direct BE; diagram shows 5. Unresolved — needs verification.',
    ]);

    expect($source->notes)->toContain('Unresolved');
});
