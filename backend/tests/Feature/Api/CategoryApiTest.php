<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists categories with a published-only article count', function () {
    $category = Category::factory()->create(['name' => 'Fundamentals', 'sort_order' => 0]);
    Article::factory()->count(2)->published()->create(['category_id' => $category->id]);
    Article::factory()->create(['category_id' => $category->id]); // draft, must not count

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'sort_order', 'articles_count']]])
        ->assertJsonPath('data.0.articles_count', 2);
});

it('returns categories ordered by sort_order and without a pagination envelope', function () {
    Category::factory()->create(['name' => 'Second', 'sort_order' => 5]);
    Category::factory()->create(['name' => 'First', 'sort_order' => 1]);

    $response = $this->getJson('/api/v1/categories')->assertOk();

    expect($response->json('data.0.name'))->toBe('First');
    expect($response->json())->not->toHaveKeys(['meta', 'links']);
});
