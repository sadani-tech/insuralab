<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ordered curated journeys through existing articles. Kept minimal: an item
     * points at an article and has a position. Fits cleanly alongside the
     * knowledge domain (ROADMAP §8).
     */
    public function up(): void
    {
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('level')->default('beginner'); // beginner | intermediate | advanced
            $table->string('status')->default('draft');    // draft | published | archived
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('learning_path_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained('learning_paths')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('note')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['learning_path_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_path_items');
        Schema::dropIfExists('learning_paths');
    }
};
