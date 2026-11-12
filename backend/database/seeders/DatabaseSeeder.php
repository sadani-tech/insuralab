<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProtectionCategorySeeder::class,
            CategorySeeder::class,
            ProductCategorySeeder::class,
            ProviderSeeder::class,
            GlossaryTermSeeder::class,
            ArticleSeeder::class,
            ProductSeeder::class,
            QuizSeeder::class,
            LearningPathSeeder::class,
            AssessmentSeeder::class,
            KnowledgeSeeder::class,
        ]);
    }
}
