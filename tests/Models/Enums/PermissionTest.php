<?php

namespace Unusualify\Modularity\Tests\Models\Enums;

use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Tests\TestCase;

class PermissionTest extends TestCase
{
    public function test_enum_cases()
    {
        $expectedCases = [
            'CREATE' => 'create',
            'VIEW' => 'view',
            'EDIT' => 'edit',
            'DELETE' => 'delete',
            'FORCEDELETE' => 'forceDelete',
            'RESTORE' => 'restore',
            'DUPLICATE' => 'duplicate',
            'REORDER' => 'reorder',
            'BULK' => 'bulk',
            'BULKDELETE' => 'bulkDelete',
            'BULKFORCEDELETE' => 'bulkForceDelete',
            'BULKRESTORE' => 'bulkRestore',
            'ACTIVITY' => 'activity',
            'SHOW' => 'show',
        ];

        foreach ($expectedCases as $caseName => $caseValue) {
            $this->assertEquals($caseValue, Permission::from($caseValue)->value);
            $this->assertEquals($caseValue, constant(Permission::class . '::' . $caseName)->value);
        }
    }

    public function test_all_cases_exist()
    {
        $cases = Permission::cases();
        $this->assertCount(14, $cases);

        $caseValues = array_map(fn ($case) => $case->value, $cases);
        $expectedValues = [
            'create', 'view', 'edit', 'delete', 'forceDelete', 'restore',
            'duplicate', 'reorder', 'bulk', 'bulkDelete', 'bulkForceDelete',
            'bulkRestore', 'activity', 'show',
        ];

        foreach ($expectedValues as $value) {
            $this->assertContains($value, $caseValues);
        }
    }

    public function test_get_static_method()
    {
        $this->assertEquals('create', Permission::get('CREATE'));
        $this->assertEquals('view', Permission::get('VIEW'));
        $this->assertEquals('edit', Permission::get('EDIT'));
        $this->assertEquals('delete', Permission::get('DELETE'));
        $this->assertEquals('forceDelete', Permission::get('FORCEDELETE'));
        $this->assertEquals('restore', Permission::get('RESTORE'));
        $this->assertEquals('duplicate', Permission::get('DUPLICATE'));
        $this->assertEquals('reorder', Permission::get('REORDER'));
        $this->assertEquals('bulk', Permission::get('BULK'));
        $this->assertEquals('bulkDelete', Permission::get('BULKDELETE'));
        $this->assertEquals('bulkForceDelete', Permission::get('BULKFORCEDELETE'));
        $this->assertEquals('bulkRestore', Permission::get('BULKRESTORE'));
        $this->assertEquals('activity', Permission::get('ACTIVITY'));
        $this->assertEquals('show', Permission::get('SHOW'));
        $this->assertNull(Permission::get('INVALID'));
    }

    public function test_from_method_with_valid_values()
    {
        $validValues = [
            'create', 'view', 'edit', 'delete', 'forceDelete', 'restore',
            'duplicate', 'reorder', 'bulk', 'bulkDelete', 'bulkForceDelete',
            'bulkRestore', 'activity', 'show',
        ];

        foreach ($validValues as $value) {
            $this->assertInstanceOf(Permission::class, Permission::from($value));
        }
    }

    public function test_from_method_with_invalid_value()
    {
        $this->expectException(\ValueError::class);
        Permission::from('invalid_permission');
    }

    public function test_try_from_method_with_valid_values()
    {
        $validValues = [
            'create', 'view', 'edit', 'delete', 'forceDelete', 'restore',
            'duplicate', 'reorder', 'bulk', 'bulkDelete', 'bulkForceDelete',
            'bulkRestore', 'activity', 'show',
        ];

        foreach ($validValues as $value) {
            $this->assertInstanceOf(Permission::class, Permission::tryFrom($value));
        }
    }

    public function test_try_from_method_with_invalid_value()
    {
        $this->assertNull(Permission::tryFrom('invalid_permission'));
    }

    public function test_enum_comparison()
    {
        $create1 = Permission::CREATE;
        $create2 = Permission::from('create');
        $view = Permission::VIEW;

        $this->assertTrue($create1 === $create2);
        $this->assertFalse($create1 === $view);
        $this->assertTrue($create1 == $create2);
        $this->assertFalse($create1 == $view);
    }

    public function test_enum_in_match_expression()
    {
        $permission = Permission::CREATE;

        $result = match ($permission) {
            Permission::CREATE => 'can_create',
            Permission::VIEW => 'can_view',
            Permission::EDIT => 'can_edit',
            Permission::DELETE => 'can_delete',
            default => 'unknown',
        };

        $this->assertEquals('can_create', $result);
    }

    public function test_enum_serialization()
    {
        $permission = Permission::EDIT;
        $serialized = serialize($permission);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(Permission::class, $unserialized);
        $this->assertTrue($permission === $unserialized);
        $this->assertEquals($permission->value, $unserialized->value);
    }

    public function test_enum_json_serialization()
    {
        $permission = Permission::VIEW;
        $json = json_encode($permission);

        $this->assertEquals('"view"', $json);
    }

    public function test_enum_name_property()
    {
        $this->assertEquals('CREATE', Permission::CREATE->name);
        $this->assertEquals('VIEW', Permission::VIEW->name);
        $this->assertEquals('EDIT', Permission::EDIT->name);
        $this->assertEquals('DELETE', Permission::DELETE->name);
        $this->assertEquals('FORCEDELETE', Permission::FORCEDELETE->name);
        $this->assertEquals('RESTORE', Permission::RESTORE->name);
        $this->assertEquals('DUPLICATE', Permission::DUPLICATE->name);
        $this->assertEquals('REORDER', Permission::REORDER->name);
        $this->assertEquals('BULK', Permission::BULK->name);
        $this->assertEquals('BULKDELETE', Permission::BULKDELETE->name);
        $this->assertEquals('BULKFORCEDELETE', Permission::BULKFORCEDELETE->name);
        $this->assertEquals('BULKRESTORE', Permission::BULKRESTORE->name);
        $this->assertEquals('ACTIVITY', Permission::ACTIVITY->name);
        $this->assertEquals('SHOW', Permission::SHOW->name);
    }

    public function test_enum_value_property()
    {
        $this->assertEquals('create', Permission::CREATE->value);
        $this->assertEquals('view', Permission::VIEW->value);
        $this->assertEquals('edit', Permission::EDIT->value);
        $this->assertEquals('delete', Permission::DELETE->value);
        $this->assertEquals('forceDelete', Permission::FORCEDELETE->value);
        $this->assertEquals('restore', Permission::RESTORE->value);
        $this->assertEquals('duplicate', Permission::DUPLICATE->value);
        $this->assertEquals('reorder', Permission::REORDER->value);
        $this->assertEquals('bulk', Permission::BULK->value);
        $this->assertEquals('bulkDelete', Permission::BULKDELETE->value);
        $this->assertEquals('bulkForceDelete', Permission::BULKFORCEDELETE->value);
        $this->assertEquals('bulkRestore', Permission::BULKRESTORE->value);
        $this->assertEquals('activity', Permission::ACTIVITY->value);
        $this->assertEquals('show', Permission::SHOW->value);
    }

    public function test_basic_crud_permissions()
    {
        $basicCrudPermissions = [
            Permission::CREATE,
            Permission::VIEW,
            Permission::EDIT,
            Permission::DELETE,
        ];

        $basicValues = ['create', 'view', 'edit', 'delete'];

        foreach ($basicCrudPermissions as $index => $permission) {
            $this->assertEquals($basicValues[$index], $permission->value);
        }
    }

    public function test_bulk_operations_permissions()
    {
        $bulkPermissions = [
            Permission::BULK,
            Permission::BULKDELETE,
            Permission::BULKFORCEDELETE,
            Permission::BULKRESTORE,
        ];

        foreach ($bulkPermissions as $permission) {
            $this->assertStringContainsString('bulk', $permission->value);
        }
    }

    public function test_advanced_permissions()
    {
        $advancedPermissions = [
            Permission::FORCEDELETE,
            Permission::RESTORE,
            Permission::DUPLICATE,
            Permission::REORDER,
            Permission::ACTIVITY,
            Permission::SHOW,
        ];

        $advancedValues = ['forceDelete', 'restore', 'duplicate', 'reorder', 'activity', 'show'];

        foreach ($advancedPermissions as $index => $permission) {
            $this->assertEquals($advancedValues[$index], $permission->value);
        }
    }

    public function test_delete_related_permissions()
    {
        $deletePermissions = [
            Permission::DELETE,
            Permission::FORCEDELETE,
            Permission::BULKDELETE,
            Permission::BULKFORCEDELETE,
        ];

        foreach ($deletePermissions as $permission) {
            $this->assertStringContainsString('delete', mb_strtolower($permission->value));
        }
    }

    public function test_restore_related_permissions()
    {
        $restorePermissions = [
            Permission::RESTORE,
            Permission::BULKRESTORE,
        ];

        foreach ($restorePermissions as $permission) {
            $this->assertStringContainsString('restore', mb_strtolower($permission->value));
        }
    }

    public function test_get_method_with_case_names()
    {
        // Test the static get method with all case names
        $cases = Permission::cases();

        foreach ($cases as $case) {
            $this->assertEquals($case->value, Permission::get($case->name));
        }
    }

    public function test_get_method_returns_null_for_invalid_case()
    {
        $this->assertNull(Permission::get('NON_EXISTENT_CASE'));
        $this->assertNull(Permission::get(''));
        $this->assertNull(Permission::get('create')); // lowercase should not match
    }

    public function test_camel_case_values()
    {
        // Test that multi-word permissions use camelCase
        $camelCasePermissions = [
            'forceDelete' => 'forceDelete',
            'bulkDelete' => 'bulkDelete',
            'bulkForceDelete' => 'bulkForceDelete',
            'bulkRestore' => 'bulkRestore',
        ];

        foreach ($camelCasePermissions as $permission => $expectedValue) {
            $this->assertEquals($expectedValue, Permission::from($permission)->value);
        }
    }

    public function test_permission_hierarchy()
    {
        // Test that we have the expected permission hierarchy
        $readPermissions = [Permission::VIEW, Permission::SHOW, Permission::ACTIVITY];
        $writePermissions = [Permission::CREATE, Permission::EDIT, Permission::DUPLICATE];
        $deletePermissions = [Permission::DELETE, Permission::FORCEDELETE];
        $bulkPermissions = [Permission::BULK, Permission::BULKDELETE, Permission::BULKFORCEDELETE, Permission::BULKRESTORE];
        $specialPermissions = [Permission::RESTORE, Permission::REORDER];

        // Ensure all permission groups are represented
        $allPermissions = array_merge($readPermissions, $writePermissions, $deletePermissions, $bulkPermissions, $specialPermissions);

        $this->assertCount(14, $allPermissions); // Should match total number of cases
    }

    public function test_no_dashboard_permission()
    {
        // Verify that DASHBOARD permission is commented out and not available
        $cases = Permission::cases();
        $caseNames = array_map(fn ($case) => $case->name, $cases);

        $this->assertNotContains('DASHBOARD', $caseNames);
    }
}
