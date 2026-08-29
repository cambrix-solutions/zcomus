<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    /**
     * Every seeded vendor owner also ends up with the `customer` role
     * (automatic on creation, see User::booted()) alongside the
     * `vendor` role granted explicitly below. That's intentional, not
     * a leftover — a vendor owner can still shop as a customer on
     * their own storefront or elsewhere.
     */
    public function run(): void
    {
        $shops = [
            ['name' => 'PP Gadgets', 'industry' => 'Electronics'],
            ['name' => 'Khmer Threads', 'industry' => 'Fashion'],
            ['name' => 'Home Corner', 'industry' => 'Home & Living'],
        ];

        foreach ($shops as $data) {
            $slug = Str::slug($data['name']);

            $vendor = User::updateOrCreate(
                ['email' => $slug . '@vendor.zcomus.test'],
                [
                    'name' => $data['name'] . ' Owner',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $vendor->assignRole('vendor');

            Shop::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $vendor->id,
                    'name' => $data['name'],
                    'industry' => $data['industry'],
                    'tagline' => $data['name'] . ' — quality you can trust',
                    'theme' => 'classic',
                    'is_active' => true,
                ]
            );
        }
    }
}
