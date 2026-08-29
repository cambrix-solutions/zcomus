<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // No explicit assignRole('customer') needed anymore —
        // User::booted() grants it automatically on creation.
        User::updateOrCreate(
            ['email' => 'sophea@customer.zcomus.test'],
            [
                'name' => 'Sophea Chan',
                'password' => Hash::make('password'),
                'phone' => '+855 12 345 678',
                'email_verified_at' => now(),
            ]
        );
    }
}
