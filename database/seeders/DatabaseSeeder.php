<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Step 0 — admin
            AdminSeeder::class,

            // Step 1 — foundation
            CategorySeeder::class,
            ShopSeeder::class,
            ProductSeeder::class,

            // Step 2 — cart & addresses
            CustomerSeeder::class,
            AddressSeeder::class,
            CartSeeder::class,

            // Step 3 — checkout & orders
            OrderSeeder::class,

            // Step 4 — account polish
            CouponSeeder::class,
            AccountPolishSeeder::class,
        ]);
    }
}
