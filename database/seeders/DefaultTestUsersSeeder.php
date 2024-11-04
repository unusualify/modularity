<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\User;

class DefaultTestUsersSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a default company
        $defaultCompany = Company::create([
            'name' => 'Default Company',
            'address' => '123 Main St',
            'city' => 'Default City',
            'state' => 'Default State',
            'country' => 'Default Country',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'vat_number' => 'VAT123456',
            'tax_id' => 'TAX123456',
        ]);

        // 2. Create users for each role
        $users = [
            // 'superadmin' => ['name' => 'Super Admin', 'email' => 'superadmin@unusualgrowth.com'],
            'admin' => ['name' => 'Admin User', 'email' => 'admin@unusualgrowth.com'],
            'manager' => ['name' => 'Manager User', 'email' => 'manager@unusualgrowth.com'],
            'editor' => ['name' => 'Editor User', 'email' => 'editor@unusualgrowth.com'],
            'reporter' => ['name' => 'Reporter User', 'email' => 'reporter@unusualgrowth.com'],
            'client-manager' => ['name' => 'Client Manager', 'email' => 'client.manager@unusualgrowth.com'],
            'client-assistant' => ['name' => 'Client Assistant', 'email' => 'client.assistant@unusualgrowth.com'],
        ];

        foreach ($users as $role => $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make("{$role}"), // Use a secure password in production
                'company_id' => null,
            ]);

            $user->assignRole($role);

            // 3. Assign company to client roles
            if (str_starts_with($role, 'client-')) {
                $user->update(['company_id' => $defaultCompany->id]);
            }
        }
    }
}
