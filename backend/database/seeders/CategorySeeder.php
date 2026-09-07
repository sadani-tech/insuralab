<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Fundamentals',
            'Life',
            'Health',
            'Critical Illness',
            'Disability & Accident',
            'Sharia',
            'Unit Link / PAYDI',
            'Policy & Process',
            'Ethics & Conduct',
            'Agency & Compensation',
        ];

        foreach ($names as $i => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i],
            );
        }
    }
}
