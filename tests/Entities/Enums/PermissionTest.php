<?php

namespace Unusualify\Modularous\Tests\Entities\Enums;

use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Tests\TestCase;

class PermissionTest extends TestCase
{
    public function test_cases_have_expected_values()
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
        $this->assertEquals('revisionApprove', Permission::REVISION_APPROVE->value);
        $this->assertEquals('revisionReject', Permission::REVISION_REJECT->value);
        $this->assertEquals('revisionRestore', Permission::REVISION_RESTORE->value);
        $this->assertEquals('activity', Permission::ACTIVITY->value);
        $this->assertEquals('show', Permission::SHOW->value);
    }

    public function test_get_resolves_by_case_name()
    {
        $this->assertEquals('create', Permission::get('CREATE'));
        $this->assertEquals('forceDelete', Permission::get('FORCEDELETE'));
        $this->assertEquals('revisionApprove', Permission::get('REVISION_APPROVE'));
    }

    public function test_get_resolves_by_case_value()
    {
        $this->assertEquals('edit', Permission::get('edit'));
        $this->assertEquals('bulkRestore', Permission::get('bulkRestore'));
    }

    public function test_get_returns_null_for_unknown_value()
    {
        $this->assertNull(Permission::get('nonexistent'));
    }

    public function test_generate_permission_name_kebab_cases_route_name()
    {
        $this->assertEquals('blog-post_create', Permission::generatePermissionName('CREATE', 'blogPost'));
        $this->assertEquals('user-profile_edit', Permission::generatePermissionName('edit', 'userProfile'));
    }

    public function test_generate_permission_middleware_definition()
    {
        $this->assertEquals(
            'can:blog-post_create',
            Permission::generatePermissionMiddlewareDefinition('CREATE', 'blogPost')
        );
        $this->assertEquals(
            'can:user-profile_delete',
            Permission::generatePermissionMiddlewareDefinition('delete', 'userProfile')
        );
    }
}
