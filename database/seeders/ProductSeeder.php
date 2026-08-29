<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::all();
        $categories = Category::all();

        if ($shops->isEmpty() || $categories->isEmpty()) {
            $this->command?->warn('Run CategorySeeder and ShopSeeder first — skipping products.');
            return;
        }

        for ($i = 1; $i <= 24; $i++) {
            $name = 'Sample Product ' . $i;

            Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'shop_id' => $shops->random()->id,
                    'category_id' => $categories->random()->id,
                    'name' => $name,
                    'brand' => 'Generic Brand',
                    'price' => fake()->randomFloat(2, 5, 300),
                    'image' => 'https://placehold.co/600x600?text=' . urlencode($name),
                    'short_description' => fake()->sentence(),
                    'description' => fake()->paragraph(),
                    'stock' => fake()->numberBetween(0, 100),
                    'status' => 'listed',
                    'is_flash' => fake()->boolean(20),
                    'is_trending' => fake()->boolean(20),
                    'is_top_selling' => fake()->boolean(20),
                ]
            );
        }
    }
}
