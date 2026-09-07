<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('level');
        });

        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('key', 100)->nullable()->after('quiz_id');
        });
        DB::statement("UPDATE quiz_questions SET key = 'question-' || id WHERE key IS NULL");
        DB::statement('ALTER TABLE quiz_questions ALTER COLUMN key SET NOT NULL');
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->unique(['quiz_id', 'key']);
        });

        Schema::table('quiz_options', function (Blueprint $table) {
            $table->string('key', 100)->nullable()->after('quiz_question_id');
        });
        DB::statement("UPDATE quiz_options SET key = 'option-' || id WHERE key IS NULL");
        DB::statement('ALTER TABLE quiz_options ALTER COLUMN key SET NOT NULL');
        Schema::table('quiz_options', function (Blueprint $table) {
            $table->unique(['quiz_question_id', 'key']);
        });

        Schema::create('quiz_question_article', function (Blueprint $table) {
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['quiz_question_id', 'article_id']);
        });

        Schema::table('learning_paths', function (Blueprint $table) {
            $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('level');
        });

        Schema::table('assessment_dimensions', function (Blueprint $table) {
            $table->foreignId('learning_path_id')->nullable()->after('assessment_id')->constrained('learning_paths')->nullOnDelete();
        });

        Schema::table('learning_path_items', function (Blueprint $table) {
            $table->string('item_type', 20)->default('article')->after('learning_path_id');
            $table->foreignId('quiz_id')->nullable()->after('article_id')->constrained('quizzes')->cascadeOnDelete();
        });
        Schema::table('learning_path_items', function (Blueprint $table) {
            $table->dropUnique(['learning_path_id', 'article_id']);
        });
        DB::statement('ALTER TABLE learning_path_items ALTER COLUMN article_id DROP NOT NULL');
        DB::statement("ALTER TABLE learning_path_items ADD CONSTRAINT learning_path_items_target_check CHECK ((item_type = 'article' AND article_id IS NOT NULL AND quiz_id IS NULL) OR (item_type = 'quiz' AND quiz_id IS NOT NULL AND article_id IS NULL))");
        Schema::table('learning_path_items', function (Blueprint $table) {
            $table->unique(['learning_path_id', 'article_id']);
            $table->unique(['learning_path_id', 'quiz_id']);
            $table->index(['learning_path_id', 'item_type', 'position']);
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE learning_path_items DROP CONSTRAINT IF EXISTS learning_path_items_target_check');
        DB::table('learning_path_items')->where('item_type', 'quiz')->delete();
        Schema::table('learning_path_items', function (Blueprint $table) {
            $table->dropIndex(['learning_path_id', 'item_type', 'position']);
            $table->dropUnique(['learning_path_id', 'quiz_id']);
            $table->dropUnique(['learning_path_id', 'article_id']);
            $table->dropConstrainedForeignId('quiz_id');
            $table->dropColumn('item_type');
        });
        DB::statement('ALTER TABLE learning_path_items ALTER COLUMN article_id SET NOT NULL');
        Schema::table('learning_path_items', function (Blueprint $table) {
            $table->unique(['learning_path_id', 'article_id']);
        });

        Schema::table('assessment_dimensions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('learning_path_id');
        });

        Schema::table('learning_paths', function (Blueprint $table) {
            $table->dropColumn('estimated_duration_minutes');
        });

        Schema::dropIfExists('quiz_question_article');

        Schema::table('quiz_options', function (Blueprint $table) {
            $table->dropUnique(['quiz_question_id', 'key']);
            $table->dropColumn('key');
        });
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropUnique(['quiz_id', 'key']);
            $table->dropColumn('key');
        });
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('estimated_duration_minutes');
        });
    }
};
