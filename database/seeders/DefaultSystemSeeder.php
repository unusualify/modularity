<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DefaultSystemSeeder extends Seeder
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

        // 2. Create permissions
        $permissions = [
            [
                'name' => 'dashboard',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'mediaLibrary',
                'guard_name' => 'unusual_users',
            ],

            ...permissionRecordsFromRoutes([
                'User',
                'Role',
                'Permission',
                'Payment',
                // 'PackageContinent',
                // 'PackageRegion',
                // 'PackageCountry',
                // 'PackageLanguage',
                // 'PackageFeature',
                // 'Package',
                // 'VatRate',
                // 'Currency',
                // 'PriceType',
                // 'Price',
            ], 'unusual_users'),
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // 3. Create roles
        $roles = [
            'superadmin', 'admin', 'manager', 'editor', 'reporter',
            'client-manager', 'client-assistant'
        ];

        $roleInstances = [];
        foreach ($roles as $role) {
            $roleInstances[$role] = Role::create(['name' => $role, 'guard_name' => 'unusual_users']);
        }

        // 4. Assign permissions to roles
        // Superadmin gets all permissions
        $roleInstances['superadmin']->givePermissionTo(Permission::all());

        // Admin gets most permissions except some sensitive ones
        $roleInstances['admin']->givePermissionTo(Permission::all()->except([
            'user_forceDelete', 'user_bulkForceDelete',
            'role_forceDelete', 'role_bulkForceDelete',
            'permission_forceDelete', 'permission_bulkForceDelete',
            'payment_forceDelete', 'payment_bulkForceDelete'
        ]));

        // Manager gets operational permissions
        $roleInstances['manager']->givePermissionTo([
            'dashboard', 'mediaLibrary',
            'user_view', 'user_create', 'user_edit',
            'payment_view', 'payment_create', 'payment_edit',
        ]);

        // Editor gets content-related permissions
        $roleInstances['editor']->givePermissionTo([
            'dashboard', 'mediaLibrary',
        ]);

        // Reporter gets view-only permissions
        $roleInstances['reporter']->givePermissionTo([
            'dashboard',
            'user_view',
            'role_view',
            'permission_view',
            'payment_view',
        ]);

        // Client Manager gets client-specific permissions
        $roleInstances['client-manager']->givePermissionTo([
            'dashboard',
            'payment_view', 'payment_create',
        ]);

        // Client Assistant gets limited client permissions
        $roleInstances['client-assistant']->givePermissionTo([
            'dashboard',
            'payment_view',
        ]);

        // 5. Create users for each role
        $users = [
            'superadmin' => ['name' => 'Super Admin', 'email' => 'superadmin@unusualgrowth.com'],
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
                'password' => Hash::make('password'), // Use a secure password in production
                'company_id' => null,
            ]);

            $user->assignRole($role);

            // 6. Assign company to client roles
            if (strpos($role, 'client-') === 0) {

                $user->update(['company_id' => $defaultCompany->id]);
            }
        }
    }
}
