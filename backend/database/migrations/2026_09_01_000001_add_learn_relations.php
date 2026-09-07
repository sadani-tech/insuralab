<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 (Learn + Glossary) additions:
 *  - articles.key_takeaways: structured "Poin Penting" list (JSON), so takeaways
 *    are data, not hardcoded in React.
 *  - glossary_term_article: an EXPLICIT link between a glossary term and the
 *    learning articles that teach it. Used for "related learning" on the glossary
 *    detail page and for the curated in-article :term[] tooltips. Deliberately
 *    explicit — InsuraLab never auto-parses article prose for glossary matches.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->jsonb('key_takeaways')->nullable()->after('body');
        });

        Schema::create('glossary_term_article', function (Blueprint $table) {
            $table->foreignId('glossary_term_id')->constrained('glossary_terms')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->primary(['glossary_term_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glossary_term_article');

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('key_takeaways');
        });
    }
};
