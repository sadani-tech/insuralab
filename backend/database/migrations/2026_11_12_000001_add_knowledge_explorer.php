<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->string('access_level')->default('unreviewed')->index();
        });
        Schema::table('glossary_terms', function (Blueprint $table) {
            $table->boolean('explorer_published')->default(false)->index();
        });
        Schema::create('source_passages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->string('key')->unique();
            $table->string('version');
            $table->string('locator');
            $table->text('body');
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
        Schema::create('knowledge_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_id')->constrained('glossary_terms')->cascadeOnDelete();
            $table->foreignId('to_id')->constrained('glossary_terms')->cascadeOnDelete();
            $table->foreignId('source_passage_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->text('explanation');
            $table->boolean('published')->default(false);
            $table->timestamps();
            $table->unique(['from_id', 'to_id']);
        });
        Schema::create('knowledge_entries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('kind')->index();
            $table->string('title');
            $table->text('summary');
            $table->longText('body');
            $table->jsonb('keywords')->nullable();
            $table->jsonb('steps')->nullable();
            $table->boolean('published')->default(false)->index();
            $table->foreignId('source_passage_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('knowledge_entry_term', function (Blueprint $table) {
            $table->foreignId('knowledge_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('glossary_term_id')->constrained()->cascadeOnDelete();
            $table->primary(['knowledge_entry_id', 'glossary_term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_entry_term');
        Schema::dropIfExists('knowledge_entries');
        Schema::dropIfExists('knowledge_relations');
        Schema::dropIfExists('source_passages');
        Schema::table('glossary_terms', fn (Blueprint $table) => $table->dropColumn('explorer_published'));
        Schema::table('sources', fn (Blueprint $table) => $table->dropColumn('access_level'));
    }
};
