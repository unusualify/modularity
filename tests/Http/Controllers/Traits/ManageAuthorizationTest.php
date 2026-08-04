<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Unusualify\Modularous\Http\Controllers\Traits\ManageAuthorization;
use Unusualify\Modularous\Tests\TestCase;

class ManageAuthorizationTest extends TestCase
{
    /** @test */
    public function it_checks_superadmin_authentication_and_roles(): void
    {
        $controller = new class
        {
            use ManageAuthorization;

            public $user;
        };

        $this->assertFalse($controller->isSuperAdmin());
        $this->assertFalse($controller->isAuthenticated());
        $this->assertFalse($controller->isAuthorized(['admin']));
        $this->assertTrue($controller->doesNotHaveAuthorization(['admin']));

        $user = new class
        {
            public bool $is_superadmin = true;

            public function hasAnyRole(array $roles): bool
            {
                return in_array('admin', $roles, true);
            }
        };

        $controller->user = $user;

        $this->assertTrue($controller->isSuperAdmin());
        $this->assertTrue($controller->isAuthenticated());
        $this->assertTrue($controller->isAuthorized(['admin']));
        $this->assertTrue($controller->hasAuthorization(['admin']));
        $this->assertFalse($controller->doesNotHaveAuthorization(['admin']));
        $this->assertFalse($controller->isAuthorized(['editor']));
    }
}
