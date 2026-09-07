<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 (Providers + Products): explicit links from a provider PRODUCT to the
 * GENERAL learning concepts and glossary terms a reader should understand first.
 * InsuraLab's hierarchy is concept → provider → product; these pivots make
 * "pahami konsepnya lebih dulu" a data relation, not hardcoded UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_article', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->primary(['product_id', 'article_id']);
        });

        Schema::create('product_glossary_term', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('glossary_term_id')->constrained('glossary_terms')->cascadeOnDelete();
            $table->primary(['product_id', 'glossary_term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_glossary_term');
        Schema::dropIfExists('product_article');
    }
};
