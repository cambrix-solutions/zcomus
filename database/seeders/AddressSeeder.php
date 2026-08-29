<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'sophea@customer.zcomus.test')->first();

        if (! $customer) {
            $this->command?->warn('Run CustomerSeeder first — skipping addresses.');
            return;
        }

        Address::updateOrCreate(
            ['user_id' => $customer->id, 'label' => 'Home'],
            [
                'full_name' => 'Sophea Chan',
                'phone' => '+855 12 345 678',
                'line1' => '#12, Street 310',
                'city' => 'Phnom Penh',
                'country' => 'KH',
                'is_default' => true,
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer->id, 'label' => 'Office'],
            [
                'full_name' => 'Sophea Chan',
                'phone' => '+855 12 345 678',
                'line1' => 'Bldg 6, Street 271',
                'city' => 'Phnom Penh',
                'country' => 'KH',
                'is_default' => false,
            ]
        );
    }
}
