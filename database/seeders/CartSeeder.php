<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'sophea@customer.zcomus.test')->first();
        $products = Product::inRandomOrder()->take(2)->get();

        if (! $customer || $products->isEmpty()) {
            $this->command?->warn('Run CustomerSeeder and ProductSeeder first — skipping cart.');
            return;
        }

        $cart = Cart::firstOrCreate(['user_id' => $customer->id]);

        foreach ($products as $product) {
            $cart->items()->updateOrCreate(
                ['product_id' => $product->id, 'variant_id' => null],
                ['qty' => rand(1, 3), 'unit_price' => $product->price]
            );
        }
    }
}
