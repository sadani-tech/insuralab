<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Protection concepts a product can respond to: life, health, critical illness,
     * disability, accident, income. These are the spine of the future Protection
     * Needs Assessment (topics, not product recommendations).
     */
    public function up(): void
    {
        Schema::create('protection_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('key')->unique(); // stable machine key: life, health, critical_illness, ...
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protection_categories');
    }
};
