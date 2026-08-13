<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test customer
        User::create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
        ]);

        // Test vendor
        User::create([
            'name' => 'Vendor',
            'email' => 'vendor@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'vendor',
        ]);

        // Test support
        User::create([
            'name' => 'Support',
            'email' => 'support@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'support',
        ]);
    }
}
