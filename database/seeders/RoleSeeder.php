<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Only the three roles web.php's check_role middleware actually
     * checks against. Admin stays a separate model/guard entirely —
     * not a row in this table.
     */
    public function run(): void
    {
        $roles = [
            ['slug' => 'customer', 'name' => 'Customer', 'description' => 'Can browse and shop.'],
            ['slug' => 'vendor', 'name' => 'Vendor', 'description' => 'Owns and manages a shop.'],
            ['slug' => 'support', 'name' => 'Support', 'description' => 'Handles customer support tickets.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
