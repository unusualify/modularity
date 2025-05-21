<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Unusualify\Modularity\Facades\Modularity;

class DefaultPermissionsSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $table = config('permission.table_names.permissions');

        Schema::disableForeignKeyConstraints();

        DB::table($table)->truncate();

        Schema::enableForeignKeyConstraints();

        $modularityAuthGuardName = Modularity::getAuthGuardName();
        DB::table($table)->insert([
            [
                'name' => 'dashboard',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'name' => 'mediaLibrary',
                'guard_name' => $modularityAuthGuardName,
            ],
            ...permissionRecordsFromRoutes([
                'User',
                'Role',
                'Permission',
                'Company',
                'VatRate',
                'Currency',
                'PriceType',
                'Payment',
                'Notification',
                'MyNotification',
            ], $modularityAuthGuardName),
        ]);

        $roleInstances = Role::all()->keyBy('name');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Assign permissions to roles
        // Admin gets most permissions except some sensitive ones
        $roleInstances['admin']->givePermissionTo(Permission::all()->except([
            'my-notification_view', 'my-notification_edit', 'my-notification_delete', 'my-notification_bulkDelete', 'my-notification_forceDelete', 'my-notification_bulkForceDelete',
            'user_forceDelete', 'user_bulkForceDelete',
            'role_forceDelete', 'role_bulkForceDelete',
            'company_forceDelete', 'company_bulkForceDelete',
            // 'permission_forceDelete', 'permission_bulkForceDelete',
            'vat-rate_forceDelete', 'vat_rate_bulkForceDelete',
            'currency_forceDelete', 'currency_bulkForceDelete',
            'price-type_forceDelete', 'price-type_bulkForceDelete',
            'payment_forceDelete', 'payment_bulkForceDelete',
        ]));

        // Manager gets operational permissions
        $roleInstances['manager']->givePermissionTo([
            'dashboard', 'mediaLibrary',
            'my-notification_view', 'my-notification_edit', 'my-notification_delete', 'my-notification_bulkDelete', 'my-notification_forceDelete', 'my-notification_bulkForceDelete',
            'user_view', 'user_create', 'user_edit',
            'vat-rate_view', 'vat-rate_create', 'vat-rate_edit',
            'currency_view', 'currency_create', 'currency_edit',
            'price-type_view', 'price-type_create', 'price-type_edit',
            'payment_view', 'payment_create', 'payment_edit',
        ]);

        // Editor gets content-related permissions
        $roleInstances['editor']->givePermissionTo([
            'dashboard', 'mediaLibrary',
            'my-notification_view', 'my-notification_edit', 'my-notification_delete', 'my-notification_bulkDelete', 'my-notification_forceDelete', 'my-notification_bulkForceDelete'

        ]);

        // Reporter gets view-only permissions
        $roleInstances['reporter']->givePermissionTo([
            'dashboard', 'mediaLibrary',
            'my-notification_view', 'my-notification_edit', 'my-notification_delete', 'my-notification_bulkDelete', 'my-notification_forceDelete', 'my-notification_bulkForceDelete'
        ]);

        // Client Manager gets client-specific permissions
        $roleInstances['client-manager']->givePermissionTo([
            'dashboard',
            'my-notification_view', 'my-notification_edit', 'my-notification_delete', 'my-notification_bulkDelete', 'my-notification_forceDelete', 'my-notification_bulkForceDelete',
            'user_view', 'user_create', 'user_edit', 'user_delete',
            'payment_view', 'payment_create',
        ]);

        // Client Assistant gets limited client permissions
        $roleInstances['client-assistant']->givePermissionTo([
            'dashboard',
            'my-notification_view', 'my-notification_edit', 'my-notification_delete', 'my-notification_bulkDelete', 'my-notification_forceDelete', 'my-notification_bulkForceDelete',
            'user_view',
            'payment_view',
        ]);

    }
}
