<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Medical / Hospitalisation', 'kind' => 'base'],
            ['name' => 'Life (Unit Link)', 'kind' => 'base'],
            ['name' => 'Sharia Life', 'kind' => 'base'],
            ['name' => 'Critical Illness Rider', 'kind' => 'rider'],
            ['name' => 'Term Life Rider', 'kind' => 'rider'],
            ['name' => 'Disability Rider', 'kind' => 'rider'],
            ['name' => 'Accident Rider', 'kind' => 'rider'],
            ['name' => 'Payor Rider', 'kind' => 'rider'],
        ];

        foreach ($rows as $i => $row) {
            ProductCategory::updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                ['name' => $row['name'], 'kind' => $row['kind'], 'sort_order' => $i],
            );
        }
    }
}
