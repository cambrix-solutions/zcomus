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
            'Electronics',
            'Fashion',
            'Home & Living',
            'Beauty & Health',
            'Groceries',
            'Sports & Outdoors',
            'Toys & Kids',
            'Automotive',
        ];

        foreach ($names as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
