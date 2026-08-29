<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * NOTE: your project also has AdminSeeder and UserSeeder (not shown
     * here — I don't have their contents). Merge those into this list
     * wherever they belong in your actual pipeline; RoleSeeder just
     * needs to run before anything that calls ->assignRole().
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            // Roles — must run before anything that assigns one
            RoleSeeder::class,

            // Step 1 — foundation
            CategorySeeder::class,
            ShopSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,

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
