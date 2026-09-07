<?php

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Exercises the migration lifecycle directly (no RefreshDatabase trait) so that
 * `down()` methods are actually run. Leaves the schema fully migrated and marks
 * RefreshDatabaseState so later test files reuse it.
 */
$expectedTables = [
    'users', 'sessions',
    'categories', 'providers', 'protection_categories', 'product_categories',
    'articles', 'tags', 'article_tag',
    'glossary_terms', 'glossary_term_related',
    'products', 'product_protection_category',
    'sources', 'sourceables',
    'quizzes', 'quiz_questions', 'quiz_options', 'quiz_question_article',
    'assessments', 'assessment_questions', 'assessment_options',
    'assessment_dimensions', 'assessment_option_impacts',
    'assessment_dimension_articles', 'assessment_option_articles',
    'learning_paths', 'learning_path_items',
];

it('migrates the full schema from scratch', function () use ($expectedTables) {
    Artisan::call('migrate:fresh', ['--force' => true]);

    foreach ($expectedTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("missing table: {$table}");
    }
});

it('rolls every migration back cleanly (down methods are valid)', function () {
    Artisan::call('migrate:fresh', ['--force' => true]);
    $exit = Artisan::call('migrate:rollback', ['--force' => true]);

    expect($exit)->toBe(0)
        ->and(Schema::hasTable('articles'))->toBeFalse()
        ->and(Schema::hasTable('products'))->toBeFalse()
        ->and(Schema::hasTable('sourceables'))->toBeFalse();
});

it('can refresh (rollback + re-migrate) end to end', function () use ($expectedTables) {
    $exit = Artisan::call('migrate:refresh', ['--force' => true]);

    expect($exit)->toBe(0);
    foreach ($expectedTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("missing table after refresh: {$table}");
    }
});

afterAll(function () {
    // Leave a clean, fully-migrated schema for the remaining test files.
    Artisan::call('migrate:fresh', ['--force' => true]);
    RefreshDatabaseState::$migrated = true;
});
