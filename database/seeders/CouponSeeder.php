<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'SAVE10'],
            [
                'title' => '10% off your order',
                'type' => 'percent',
                'value' => 10,
                'min_order' => 20,
                'rule' => 'Min. spend $20',
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'WELCOME5'],
            [
                'title' => '$5 off first order',
                'type' => 'fixed',
                'value' => 5,
                'min_order' => null,
                'rule' => 'First order only',
                'starts_at' => now()->subDays(30),
                'ends_at' => now()->addDays(60),
                'is_active' => true,
            ]
        );
    }
}
