<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Gives about a third of the seeded products a couple of color
     * variants, so GET /api/products/{id} has something real to show
     * in `variants[]` and `colors[]` when you're testing in Postman.
     */
    public function run(): void
    {
        $colors = ['Black', 'White', 'Blue'];

        Product::inRandomOrder()->take(8)->get()->each(function (Product $product) use ($colors) {
            $picked = array_slice($colors, 0, rand(2, 3));

            foreach ($picked as $color) {
                $product->variants()->firstOrCreate(
                    ['sku' => strtoupper($product->slug) . '-' . strtoupper($color)],
                    [
                        'color' => $color,
                        'stock' => rand(0, 20),
                        'price' => null, // same as product price
                    ]
                );
            }

            $product->update(['colors' => $picked]);
        });
    }
}
