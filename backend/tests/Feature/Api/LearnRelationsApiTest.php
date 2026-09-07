<?php

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\GlossaryTerm;
use App\Models\LearningPath;
use App\Models\Quiz;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists published learning paths with lesson and quiz counts', function () {
    $path = LearningPath::create([
        'title' => 'Mulai dari Dasar', 'slug' => 'mulai-dari-dasar',
        'description' => 'Urutan singkat.', 'level' => ContentLevel::Beginner,
        'status' => ContentStatus::Published, 'sort_order' => 0,
    ]);
    LearningPath::create([
        'title' => 'Draf', 'slug' => 'draf', 'level' => ContentLevel::Beginner,
        'status' => ContentStatus::Draft, 'sort_order' => 1,
    ]);
    $a = Article::factory()->published()->create();
    $path->items()->create(['item_type' => 'article', 'article_id' => $a->id, 'position' => 0]);
    $quiz = Quiz::create(['title' => 'Quiz', 'slug' => 'quiz', 'level' => 'beginner', 'status' => ContentStatus::Published]);
    $path->items()->create(['item_type' => 'quiz', 'quiz_id' => $quiz->id, 'position' => 1]);

    $data = $this->getJson('/api/v1/learning-paths')->assertOk()
        ->assertJsonStructure(['data' => [['id', 'title', 'slug', 'level', 'estimated_duration_minutes', 'lessons_count', 'quizzes_count']]])
        ->json('data');

    expect(collect($data)->pluck('slug'))->toContain('mulai-dari-dasar')->not->toContain('draf');
    expect(collect($data)->firstWhere('slug', 'mulai-dari-dasar')['lessons_count'])->toBe(1)
        ->and(collect($data)->firstWhere('slug', 'mulai-dari-dasar')['quizzes_count'])->toBe(1);
});

it('returns an ordered mixed learning path with only public targets', function () {
    $path = LearningPath::create([
        'title' => 'Jalur', 'slug' => 'jalur', 'level' => ContentLevel::Beginner,
        'status' => ContentStatus::Published, 'sort_order' => 0,
    ]);
    $first = Article::factory()->published()->create(['title' => 'Langkah Satu']);
    $draft = Article::factory()->create(['status' => ContentStatus::Draft, 'title' => 'Langkah Rahasia']);
    $second = Article::factory()->published()->create(['title' => 'Langkah Dua']);
    $quiz = Quiz::create(['title' => 'Uji Pemahaman', 'slug' => 'uji', 'level' => 'beginner', 'status' => ContentStatus::Published]);
    $path->items()->create(['item_type' => 'article', 'article_id' => $first->id, 'position' => 0]);
    $path->items()->create(['item_type' => 'article', 'article_id' => $draft->id, 'position' => 1]);
    $path->items()->create(['item_type' => 'quiz', 'quiz_id' => $quiz->id, 'position' => 2]);
    $path->items()->create(['item_type' => 'article', 'article_id' => $second->id, 'position' => 3]);

    $response = $this->getJson('/api/v1/learning-paths/jalur')->assertOk();

    $items = $response->json('data.items');
    expect($items)->toHaveCount(3);
    expect($items[0]['position'])->toBe(1)
        ->and($items[0]['type'])->toBe('article')
        ->and($items[0]['article']['slug'])->toBe($first->slug)
        ->and($items[1]['position'])->toBe(2)
        ->and($items[1]['type'])->toBe('quiz')
        ->and($items[1]['quiz']['slug'])->toBe('uji')
        ->and($items[1]['quiz']['category'])->toBeNull()
        ->and($items[1]['quiz']['related_learning_paths'])->toBe([])
        ->and($items[2]['position'])->toBe(3)
        ->and($items[2]['article']['slug'])->toBe($second->slug);
    expect($response->getContent())->not->toContain('Langkah Rahasia');
});

it('404s for an unknown or draft learning path', function () {
    LearningPath::create([
        'title' => 'Draf', 'slug' => 'draf', 'level' => ContentLevel::Beginner,
        'status' => ContentStatus::Draft, 'sort_order' => 0,
    ]);

    $this->getJson('/api/v1/learning-paths/draf')->assertNotFound();
    $this->getJson('/api/v1/learning-paths/tidak-ada')->assertNotFound();
});

it('exposes key_takeaways, curated glossary terms and deterministic related articles on an article', function () {
    $category = Category::factory()->create(['slug' => 'fundamentals']);
    $tag = Tag::create(['name' => 'dasar', 'slug' => 'dasar']);

    $term = GlossaryTerm::factory()->create(['term' => 'Premi', 'slug' => 'premi']);

    $article = Article::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Apa Itu Asuransi',
        'key_takeaways' => ['Poin satu.', 'Poin dua.'],
    ]);
    $article->tags()->attach($tag->id);
    $article->glossaryTerms()->attach($term->id);

    $sibling = Article::factory()->published()->create(['category_id' => $category->id, 'title' => 'Cara Kerja Asuransi']);
    $sibling->tags()->attach($tag->id);
    Article::factory()->published()->create(['title' => 'Topik Tak Terkait']);

    $response = $this->getJson("/api/v1/articles/{$article->slug}")->assertOk();

    expect($response->json('data.key_takeaways'))->toBe(['Poin satu.', 'Poin dua.']);
    expect($response->json('data.glossary_terms.0.slug'))->toBe('premi');
    expect(collect($response->json('data.related'))->pluck('slug'))
        ->toContain($sibling->slug)
        ->not->toContain($article->slug);
});

it('returns related articles and related terms on a glossary term', function () {
    $term = GlossaryTerm::factory()->create(['term' => 'Underwriting', 'slug' => 'underwriting']);
    $related = GlossaryTerm::factory()->create(['term' => 'Moral Hazard', 'slug' => 'moral-hazard']);
    $term->relatedTerms()->attach($related->id);

    $article = Article::factory()->published()->create(['title' => 'Underwriting']);
    $draft = Article::factory()->create(['status' => ContentStatus::Draft, 'title' => 'Draf Underwriting']);
    $term->articles()->attach([$article->id, $draft->id]);

    $response = $this->getJson('/api/v1/glossary/underwriting')->assertOk()
        ->assertJsonStructure(['data' => ['related_terms', 'related_articles']]);

    expect(collect($response->json('data.related_articles'))->pluck('slug'))
        ->toContain($article->slug)->not->toContain($draft->slug);
    expect($response->json('data.related_terms.0.slug'))->toBe('moral-hazard');
});

it('includes an articles_count on the glossary list', function () {
    $term = GlossaryTerm::factory()->create(['slug' => 'klaim', 'term' => 'Klaim']);
    $a = Article::factory()->published()->create();
    $term->articles()->attach($a->id);

    $this->getJson('/api/v1/glossary')
        ->assertOk()
        ->assertJsonPath('data.0.articles_count', 1);
});
