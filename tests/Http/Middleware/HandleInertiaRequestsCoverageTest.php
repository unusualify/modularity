<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Middleware\HandleInertiaRequests;
use Unusualify\Modularous\Support\ModularousFlashWarnings;
use Unusualify\Modularous\Tests\TestCase;

class HandleInertiaRequestsCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeMiddleware(): HandleInertiaRequestsTestable
    {
        return new HandleInertiaRequestsTestable;
    }

    private function registerStoreRoutes(): void
    {
        if (! Route::has('admin.profile.update')) {
            Route::put('/admin/profile', fn () => 'ok')->name('admin.profile.update');
        }
        if (! Route::has('admin.login')) {
            Route::get('/admin/login', fn () => 'ok')->name('admin.login');
        }
        if (! Route::has('admin.profile.ui-preferences')) {
            Route::post('/admin/profile/ui-preferences', fn () => 'ok')->name('admin.profile.ui-preferences');
        }
    }

    /** @test */
    public function version_root_view_and_share_work_for_guest(): void
    {
        Modularous::shouldReceive('getAuthGuardName')->andReturn('modularous');
        Config::set('modularous.js_namespace', 'Modularous');
        Config::set('modularous.timezone', 'UTC');
        Config::set('modularous.enabled.media-library', false);
        Config::set('modularous.enabled.file-library', false);
        Config::set('modularous.ui_settings.sidebar', ['width' => 260]);
        Config::set('modularous.ui_settings.secondarySidebar', []);
        Config::set('modularous.ui_settings.topbar', []);
        Config::set('modularous.ui_settings.bottomNavigation', []);
        Config::set('modularous.media_library.show_file_name', false);

        $this->registerStoreRoutes();

        $request = Request::create('/admin', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('message', 'hi');
        $request->session()->put(ModularousFlashWarnings::SESSION_KEY, [['text' => 'warn']]);
        $request->attributes->set('endpoints', (object) ['a' => 1]);

        $middleware = $this->makeMiddleware();

        $this->assertSame('modularous::layouts.app-inertia', $middleware->rootView($request));
        $this->assertNull($middleware->version($request));

        $shared = $middleware->share($request);
        $this->assertArrayHasKey('auth', $shared);
        $this->assertNull($shared['auth']['user']);
        $this->assertArrayHasKey('flash', $shared);
        $this->assertSame('hi', ($shared['flash']['message'])());
        $this->assertSame([['text' => 'warn']], ($shared['flash']['warnings'])());
        $this->assertSame('Modularous', $shared['config']['js_namespace']);
        $this->assertSame([], ($shared['authorization'])());

        $store = ($shared['storeData'])();
        $this->assertTrue($store['user']['isGuest']);
        $this->assertSame([], $store['medias']['types']);
        $this->assertSame([], $store['languages']['all']);
        $this->assertTrue($store['ambient']['test']);
    }

    /** @test */
    public function authorization_and_store_data_include_authenticated_user(): void
    {
        Modularous::shouldReceive('getAuthGuardName')->andReturn('modularous');
        Config::set('modularous.enabled.media-library', false);
        Config::set('modularous.enabled.file-library', false);
        Config::set('modularous.ui_settings.sidebar', []);
        Config::set('modularous.ui_settings.secondarySidebar', []);
        Config::set('modularous.ui_settings.topbar', []);
        Config::set('modularous.ui_settings.bottomNavigation', []);
        Config::set('modularous.media_library.show_file_name', true);

        $this->registerStoreRoutes();

        $permissions = new Collection([(object) ['name' => 'item_view']]);
        $roles = new Collection([(object) ['name' => 'admin']]);

        $user = Mockery::mock();
        $user->is_superadmin = true;
        $user->is_client = false;
        $user->shouldReceive('isClient')->andReturn(false);
        $user->shouldReceive('hasRestorable')->andReturn(true);
        $user->shouldReceive('hasBulkable')->andReturn(false);
        $user->shouldReceive('getAllPermissions')->andReturn($permissions);
        $user->roles = $roles;
        $user->shouldReceive('toArray')->andReturn(['id' => 7, 'name' => 'Ada']);

        $request = Request::create('/admin', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $request->setUserResolver(function ($guard = null) use ($user) {
            return $guard === 'modularous' ? $user : null;
        });

        $middleware = $this->makeMiddleware();
        $auth = $middleware->exposeGetAuthorizationData($request);
        $this->assertTrue($auth['isSuperAdmin']);
        $this->assertSame(['item_view'], $auth['permissions']);
        $this->assertSame(['admin'], $auth['roles']);

        $store = $middleware->exposeGetStoreData($request);
        $this->assertFalse($store['user']['isGuest']);
        $this->assertSame(7, $store['user']['profile']['id']);
        $this->assertTrue($store['medias']['showFileName']);
    }
}

class HandleInertiaRequestsTestable extends HandleInertiaRequests
{
    public function rootView(Request $request): string
    {
        return $this->rootView;
    }

    public function exposeGetAuthorizationData(Request $request): array
    {
        return $this->getAuthorizationData($request);
    }

    public function exposeGetStoreData(Request $request): array
    {
        return $this->getStoreData($request);
    }
}
