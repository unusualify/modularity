<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ModuleRouteInspect;

use Illuminate\Foundation\Auth\User;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspector;
use Unusualify\Modularous\Tests\TestCase;

class ModuleRouteInspectorAccessTest extends TestCase
{
    /** @test */
    public function user_cannot_access_when_feature_disabled(): void
    {
        config([
            'modularous.module_route_inspect.enabled' => false,
            'modularous.module_route_inspect.allowed_roles' => ['superadmin'],
        ]);

        $user = new class extends User
        {
            public bool $is_superadmin = true;
        };

        $inspector = $this->app->make(ModuleRouteInspector::class);

        $this->assertFalse($inspector->userCanAccess($user));
    }

    /** @test */
    public function superadmin_can_access_when_enabled(): void
    {
        config([
            'modularous.module_route_inspect.enabled' => true,
            'modularous.module_route_inspect.allowed_roles' => ['superadmin'],
        ]);

        $user = new class extends User
        {
            public bool $is_superadmin = true;
        };

        $inspector = $this->app->make(ModuleRouteInspector::class);

        $this->assertTrue($inspector->userCanAccess($user));
        $this->assertTrue($inspector->isSuperadmin($user));
    }

    /** @test */
    public function empty_panel_endpoints_are_blank_strings(): void
    {
        $inspector = $this->app->make(ModuleRouteInspector::class);
        $endpoints = $inspector->emptyPanelEndpoints();

        $this->assertSame('', $endpoints['inspect']);
        $this->assertSame('', $endpoints['setStatus']);
    }

    /** @test */
    public function status_toggle_respects_config_flag(): void
    {
        config(['modularous.module_route_inspect.allow_status_toggle' => false]);

        $inspector = $this->app->make(ModuleRouteInspector::class);

        $this->assertFalse($inspector->canToggleStatus());

        $this->expectException(\RuntimeException::class);
        $inspector->setRouteEnabled('Blog', 'Post', true);
    }
}
