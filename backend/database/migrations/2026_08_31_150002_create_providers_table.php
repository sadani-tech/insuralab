<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->text('presence_in_indonesia')->nullable();
            $table->string('website_url')->nullable();
            $table->string('status')->default('active'); // active | inactive
            $table->date('last_reviewed_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
