<?php

namespace Database\Seeders;

use App\Models\ProtectionCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProtectionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'life', 'name' => 'Life', 'short_description' => 'Replacing household income and covering obligations if the earner dies.'],
            ['key' => 'health', 'name' => 'Health', 'short_description' => 'Reducing the impact of hospitalisation and medical treatment costs.'],
            ['key' => 'critical_illness', 'name' => 'Critical Illness', 'short_description' => 'A lump-sum benefit on diagnosis of a covered serious condition.'],
            ['key' => 'disability', 'name' => 'Disability', 'short_description' => 'Support when a permanent disability ends the ability to earn.'],
            ['key' => 'accident', 'name' => 'Accident', 'short_description' => 'Additional benefit for accidental death or bodily injury.'],
            ['key' => 'income', 'name' => 'Income & Sustainability', 'short_description' => 'Keeping premiums paid and protection in force over the long term.'],
        ];

        foreach ($rows as $i => $row) {
            ProtectionCategory::updateOrCreate(
                ['key' => $row['key']],
                [
                    'name' => $row['name'],
                    'slug' => Str::slug($row['name']),
                    'short_description' => $row['short_description'],
                    'description' => $row['short_description'],
                    'sort_order' => $i,
                ],
            );
        }
    }
}
