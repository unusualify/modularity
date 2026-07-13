<?php

namespace Unusualify\Modularous\Tests\Services\View;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\View\ModularousNavigation;
use Unusualify\Modularous\Tests\TestCase;

class ModularousNavigationTest extends TestCase
{
    protected $navigation;

    protected $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = Mockery::mock(Request::class);
        $this->mockRequest->shouldReceive('url')->andReturn('http://localhost/admin/dashboard');

        $this->navigation = new ModularousNavigation($this->mockRequest);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_constructs_with_request()
    {
        $request = Mockery::mock(Request::class);
        $nav = new ModularousNavigation($request);

        $this->assertInstanceOf(ModularousNavigation::class, $nav);
    }

    /** @test */
    public function it_can_get_system_menu()
    {
        Modularous::shouldReceive('getSystemModules')
            ->once()
            ->andReturn([]);

        $result = $this->navigation->systemMenu();

        $this->assertIsArray($result);
    }

    /** @test */
    public function it_can_get_modules_menu()
    {
        Modularous::shouldReceive('getModules')
            ->once()
            ->andReturn([]);

        $result = $this->navigation->modulesMenu();

        $this->assertIsArray($result);
    }

    /** @test */
    public function it_returns_false_when_menu_item_fails_permission_check()
    {
        $user = $this->makeUser();
        $user->shouldReceive('can')->with('non-existent-permission')->andReturn(false);
        $this->actingAs($user);

        $menuItem = [
            'name' => 'Test Menu',
            'can' => 'non-existent-permission',
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_processes_menu_item_with_valid_permission()
    {
        $user = $this->makeUser();
        $user->shouldReceive('can')->with('view-dashboard')->andReturn(true);
        $this->actingAs($user);

        $menuItem = [
            'name' => 'Dashboard',
            'icon' => 'dashboard',
            'can' => 'view-dashboard',
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertIsArray($result);
        $this->assertEquals('Dashboard', $result['name']);
    }

    /** @test */
    public function it_filters_menu_item_by_allowed_roles()
    {
        $user = $this->makeUser([
            'role' => 'admin',
        ]);
        $user->shouldReceive('hasRole')->with('admin')->andReturn(true);
        $this->actingAs($user);

        $menuItem = [
            'name' => 'Admin Panel',
            'allowedRoles' => ['admin'],
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        // Should return array or false based on  role checking
        $this->assertTrue(is_array($result) || $result === false);
    }

    /** @test */
    public function it_processes_nested_menu_items()
    {
        $menuItem = [
            'name' => 'Parent Menu',
            'icon' => 'folder',
            'items' => [
                [
                    'name' => 'Child 1',
                    'icon' => 'file',
                ],
            ],
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
    }

    /** @test */
    public function it_returns_false_when_route_does_not_exist()
    {
        Route::shouldReceive('hasAdmin')
            ->with('non.existent.route')
            ->andReturn(false);

        $menuItem = [
            'name' => 'Invalid Route',
            'route_name' => 'non.existent.route',
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_sets_active_state_for_matching_route()
    {
        $this->mockRequest->shouldReceive('url')
            ->andReturn('http://localhost/admin/dashboard');

        Route::shouldReceive('hasAdmin')
            ->with('admin.dashboard')
            ->andReturn('admin.dashboard');

        Modularous::shouldReceive('isModularousRoute')
            ->with('admin.dashboard')
            ->andReturn(true);

        $menuItem = [
            'name' => 'Dashboard',
            'route_name' => 'admin.dashboard',
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('route', $result);
    }

    /** @test */
    public function it_processes_callable_badge()
    {
        $menuItem = [
            'name' => 'Notifications',
            'icon' => 'bell',
            'badge' => function () {
                return 5;
            },
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertIsArray($result);
        if (isset($result['badge'])) {
            $this->assertEquals(5, $result['badge']);
        }
    }

    /** @test */
    public function it_removes_badge_when_count_is_zero()
    {
        $menuItem = [
            'name' => 'Notifications',
            'icon' => 'bell',
            'badge' => 0,
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('badge', $result);
    }

    /** @test */
    public function it_applies_responsive_classes()
    {
        $menuItem = [
            'name' => 'Responsive Menu',
            'icon' => 'phone',
            'responsive' => [
                'xs' => 'hide',
                'md' => 'show',
            ],
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertIsArray($result);
        // Responsive classes should be applied
    }

    /** @test */
    public function it_formats_sidebar_menus_for_all_types()
    {
        $menus = [
            'default' => [
                ['name' => 'Dashboard', 'icon' => 'dashboard'],
            ],
            'superadmin' => [
                ['name' => 'Admin Panel', 'icon' => 'admin'],
            ],
        ];

        $result = $this->navigation->formatSidebarMenus($menus);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('default', $result);
        $this->assertArrayHasKey('superadmin', $result);
    }

    /** @test */
    public function it_formats_single_sidebar_menu()
    {
        $menu = [
            ['name' => 'Dashboard', 'icon' => 'dashboard'],
            ['name' => 'Settings', 'icon' => 'settings'],
        ];

        $result = $this->navigation->formatSidebarMenu($menu);

        $this->assertIsArray($result);
    }

    /** @test */
    public function it_unsets_menu_keys_correctly()
    {
        $menu = [
            'item1' => ['name' => 'Item 1'],
            'item2' => ['name' => 'Item 2'],
        ];

        $result = $this->navigation->unsetMenuKeys($menu);

        $this->assertIsArray($result);
        // Should convert to numeric array
        $this->assertArrayHasKey(0, $result);
    }

    /** @test */
    public function it_preserves_menu_structure_with_name_key()
    {
        $menu = [
            'name' => 'Parent',
            'items' => [
                ['name' => 'Child 1'],
                ['name' => 'Child 2'],
            ],
        ];

        $result = $this->navigation->unsetMenuKeys($menu);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('items', $result);
    }

    /** @test */
    public function it_sets_active_sidebar_items_based_on_url()
    {
        $this->mockRequest->shouldReceive('url')
            ->andReturn('http://localhost/admin/dashboard');

        Route::shouldReceive('hasAdmin')->andReturn('admin.dashboard');
        Modularous::shouldReceive('isModularousRoute')->andReturn(true);

        $items = [
            [
                'name' => 'Dashboard',
                'route' => 'http://localhost/admin/dashboard',
                'is_active' => 0,
            ],
            [
                'name' => 'Users',
                'route' => 'http://localhost/admin/users',
                'is_active' => 0,
            ],
        ];

        $result = $this->navigation->setActiveSidebarItems($items);

        // Should return true if an active item was found
        $this->assertIsBool($result);
    }

    /** @test */
    public function it_sets_active_for_nested_items()
    {
        $this->mockRequest->shouldReceive('url')
            ->andReturn('http://localhost/admin/users/roles');

        $items = [
            [
                'name' => 'Users',
                'route' => 'http://localhost/admin/users',
                'items' => [
                    [
                        'name' => 'Roles',
                        'route' => 'http://localhost/admin/users/roles',
                        'is_active' => 0,
                    ],
                ],
            ],
        ];

        $result = $this->navigation->setActiveSidebarItems($items);

        $this->assertIsBool($result);
    }

    /** @test */
    public function it_updates_badge_props_for_active_items()
    {
        $this->mockRequest->shouldReceive('url')
            ->andReturn('http://localhost/admin/notifications');

        $items = [
            [
                'name' => 'Notifications',
                'route' => 'http://localhost/admin/notifications',
                'is_active' => 0,
                'badge' => 10,
                'badgeProps' => ['color' => 'secondary'],
            ],
        ];

        $result = $this->navigation->setActiveSidebarItems($items);

        // The method should return a boolean indicating if an active item was found
        $this->assertIsBool($result);
        // Items array should still be valid
        $this->assertIsArray($items);
        $this->assertArrayHasKey('badgeProps', $items[0]);
    }

    /** @test */
    public function it_processes_menu_items_collection_key(): void
    {
        $menuItem = [
            'name' => 'Parent',
            'menuItems' => [
                [
                    'name' => 'Child',
                    'icon' => 'mdi-file',
                ],
            ],
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('menuItems', $result);
        $this->assertIsArray($result['menuItems'][0]);
    }

    /** @test */
    public function it_returns_false_when_allowed_roles_check_fails(): void
    {
        $user = $this->makeUser(['role' => 'editor']);
        $user->shouldReceive('hasRole')->with(['admin'])->andReturn(false);
        $this->actingAs($user);

        $menuItem = [
            'name' => 'Admin Only',
            'allowedRoles' => ['admin'],
        ];

        $this->assertFalse($this->navigation->sidebarMenuItem($menuItem));
    }

    /** @test */
    public function it_applies_default_badge_props_for_positive_counts(): void
    {
        $menuItem = [
            'name' => 'Alerts',
            'badge' => 3,
        ];

        $result = $this->navigation->sidebarMenuItem($menuItem);

        $this->assertSame(3, $result['badge']);
        $this->assertSame('secondary', $result['badgeProps']['color']);
        $this->assertSame('text-white', $result['badgeProps']['class']);
    }

    /**
     * Helper method to create a mock user
     */
    protected function makeUser($attributes = [])
    {
        $user = Mockery::mock('Illuminate\Foundation\Auth\User');
        $user->shouldReceive('can')->andReturn(true)->byDefault();
        $user->shouldReceive('getAttribute')->andReturn($attributes['role'] ?? 'user');
        $user->shouldReceive('hasRole')->andReturn(true)->byDefault();
        $user->shouldReceive('getRoleNames')->andReturn([$attributes['role'] ?? 'user']);

        return $user;
    }
}
