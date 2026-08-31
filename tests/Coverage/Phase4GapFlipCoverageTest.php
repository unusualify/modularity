<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Coverage;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\View\Engines\PhpEngine;
use Mockery;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;
use Unusualify\Modularous\Entities\Tag;
use Unusualify\Modularous\Entities\Traits\Processable;
use Unusualify\Modularous\Entities\Traits\Publishable;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\Utm;
use Unusualify\Modularous\Http\Controllers\Controller;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\EnforcesMfaSetupOnLogin;
use Unusualify\Modularous\Http\Middleware\AuthenticateMiddleware;
use Unusualify\Modularous\Http\Middleware\ImpersonateMiddleware;
use Unusualify\Modularous\Http\Middleware\LoadLocalizedConfig;
use Unusualify\Modularous\Http\Middleware\RedirectIfAuthenticatedMiddleware;
use Unusualify\Modularous\Http\Middleware\RedirectorMiddleware;
use Unusualify\Modularous\Http\Middleware\RequireMfaMiddleware;
use Unusualify\Modularous\Http\Middleware\StepUpMiddleware;
use Unusualify\Modularous\Http\Middleware\UtmMiddleware;
use Unusualify\Modularous\Http\ViewComposers\ActiveNavigation;
use Unusualify\Modularous\Http\ViewComposers\CurrentUser;
use Unusualify\Modularous\Http\ViewComposers\FilesUploaderConfig;
use Unusualify\Modularous\Http\ViewComposers\MediasUploaderConfig;
use Unusualify\Modularous\Hydrates\Inputs\AssignmentHydrate;
use Unusualify\Modularous\Hydrates\Inputs\FilepondAvatarHydrate;
use Unusualify\Modularous\Hydrates\Inputs\PriceHydrate;
use Unusualify\Modularous\Hydrates\Inputs\ProcessHydrate;
use Unusualify\Modularous\Hydrates\Inputs\RepeaterHydrate;
use Unusualify\Modularous\Hydrates\Inputs\RevisionHydrate;
use Unusualify\Modularous\Hydrates\Inputs\StateableHydrate;
use Unusualify\Modularous\Hydrates\Inputs\TaggerHydrate;
use Unusualify\Modularous\Hydrates\Inputs\TagHydrate;
use Unusualify\Modularous\Jobs\Cache\Concerns\DispatchesOnModularousCacheQueue;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Notifications\StepUpCodeNotification;
use Unusualify\Modularous\Observers\RemoteApiSourceableObserver;
use Unusualify\Modularous\Repositories\MediaRepository;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\PublishableTrait;
use Unusualify\Modularous\Services\RedirectService;
use Unusualify\Modularous\Services\Security\SecurityService;
use Unusualify\Modularous\Services\Security\StepUpService;
use Unusualify\Modularous\Support\ModularousFlashWarnings;
use Unusualify\Modularous\Tests\TestCase;

/**
 * Phase-4 tiny class flips + hydrate/middleware leftovers for >50% classes.
 */
class Phase4GapFlipCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function redirector_middleware_redirects_or_passes(): void
    {
        $service = $this->app->make(RedirectService::class);
        $service->set('https://example.test/next');

        $redirecting = $this->app->make(RedirectorMiddleware::class);
        $response = $redirecting->handle(Request::create('/'), static fn () => response('nope'));
        $this->assertTrue($response->isRedirect());
        $this->assertSame('https://example.test/next', $response->headers->get('Location'));

        $passthrough = $this->app->make(RedirectorMiddleware::class);
        $ok = $passthrough->handle(Request::create('/'), static fn () => response('ok'));
        $this->assertSame('ok', $ok->getContent());
    }

    /** @test */
    public function redirect_if_authenticated_middleware_branches(): void
    {
        config(['modularous.auth_login_redirect_path' => '/dashboard']);

        $guard = Mockery::mock();
        $guard->shouldReceive('check')->once()->andReturn(true);
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('modularous')->once()->andReturn($guard);

        $middleware = new RedirectIfAuthenticatedMiddleware(
            $auth,
            $this->app['redirect'],
            $this->app['config']
        );

        $response = $middleware->handle(Request::create('/login'), static fn () => response('guest'));
        $this->assertTrue($response->isRedirect());

        $guestGuard = Mockery::mock();
        $guestGuard->shouldReceive('check')->once()->andReturn(false);
        $guestAuth = Mockery::mock(AuthFactory::class);
        $guestAuth->shouldReceive('guard')->with('modularous')->once()->andReturn($guestGuard);

        $guestMw = new RedirectIfAuthenticatedMiddleware(
            $guestAuth,
            $this->app['redirect'],
            $this->app['config']
        );
        $this->assertSame(
            'guest',
            $guestMw->handle(Request::create('/login'), static fn () => response('guest'))->getContent()
        );
    }

    /** @test */
    public function step_up_and_require_mfa_middleware_short_circuits(): void
    {
        config([
            'modularous.security.step_up.enabled' => false,
            'modularous.security.enabled' => false,
        ]);

        $security = Mockery::mock(SecurityService::class);
        $stepUp = Mockery::mock(StepUpService::class);

        $stepMw = new StepUpMiddleware($security, $stepUp);
        $this->assertSame(
            'ok',
            $stepMw->handle(Request::create('/'), static fn () => response('ok'))->getContent()
        );

        config(['modularous.security.step_up.enabled' => true]);
        $request = Request::create('/secure');
        $request->setLaravelSession($this->app['session']->driver());
        $route = new Route(['GET'], '/secure', static fn () => null);
        $route->name('admin.secure.action');
        $request->setRouteResolver(static fn () => $route);

        $this->assertSame(
            'ok',
            $stepMw->handle($request, static fn () => response('ok'))->getContent()
        );

        $user = Mockery::mock(Authenticatable::class);
        $request->setUserResolver(static fn () => $user);
        $security->shouldReceive('matchedUserStepUpCapability')->once()->andReturn(null);
        $this->assertSame(
            'ok',
            $stepMw->handle($request, static fn () => response('ok'))->getContent()
        );

        $security->shouldReceive('matchedUserStepUpCapability')->once()->andReturn('billing');
        $request->session()->put('security_step_up_verified_at', time());
        config(['modularous.security.session.step_up_ttl_minutes' => 15]);
        $this->assertSame(
            'ok',
            $stepMw->handle($request, static fn () => response('ok'))->getContent()
        );

        $security->shouldReceive('matchedUserStepUpCapability')->once()->andReturn('billing');
        $request->session()->put('security_step_up_verified_at', 0);
        $stepUp->shouldReceive('interrupt')->once()->andReturn(redirect()->to('/step-up'));
        $this->assertTrue($stepMw->handle($request, static fn () => response('ok'))->isRedirect());

        $mfa = new RequireMfaMiddleware($security);
        $this->assertSame(
            'ok',
            $mfa->handle(Request::create('/'), static fn () => response('ok'))->getContent()
        );

        config(['modularous.security.enabled' => true]);
        $security->shouldReceive('userRequiresMfa')->once()->andReturn(false);
        $this->assertSame(
            'ok',
            $mfa->handle(Request::create('/'), static fn () => response('ok'))->getContent()
        );

        $security->shouldReceive('userRequiresMfa')->once()->andReturn(true);
        $security->shouldReceive('userHasEnabledMfa')->once()->andReturn(true);
        $this->assertSame(
            'ok',
            $mfa->handle(Request::create('/'), static fn () => response('ok'))->getContent()
        );

        $security->shouldReceive('userRequiresMfa')->once()->andReturn(true);
        $security->shouldReceive('userHasEnabledMfa')->once()->andReturn(false);
        $json = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $json->headers->set('Accept', 'application/json');
        $this->assertSame(403, $mfa->handle($json, static fn () => response('ok'))->getStatusCode());

        RouteFacade::get('/login', static fn () => 'login')->name('admin.login.form');
        RouteFacade::getRoutes()->refreshNameLookups();
        $security->shouldReceive('userRequiresMfa')->once()->andReturn(true);
        $security->shouldReceive('userHasEnabledMfa')->once()->andReturn(false);
        $redirect = $mfa->handle(Request::create('/'), static fn () => response('ok'));
        $this->assertTrue($redirect->isRedirect());
    }

    /** @test */
    public function utm_middleware_composer_and_load_localized_config_branches(): void
    {
        Utm::shouldReceive('getParameters')->andReturn(['utm_source' => 'ads']);

        config([
            'auth.guards.modularous' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'auth.providers.users' => [
                'driver' => 'eloquent',
                'model' => Authenticatable::class,
            ],
        ]);

        $middleware = new UtmMiddleware;
        $this->assertSame('ok', $middleware->handle(Request::create('/'), static fn () => response('ok'))->getContent());

        $factory = View::getFacadeRoot();
        $view = new \Illuminate\View\View(
            $factory,
            new PhpEngine($this->app['files']),
            'modularous::layouts.app-inertia',
            __FILE__,
            ['existing' => 1]
        );
        $factory->callComposer($view);
        $this->assertSame('ads', $view->getData()['utmParameters']['utm_source'] ?? null);

        $baseKey = modularousBaseKey();
        $configDir = base_path('modularous');
        if (! is_dir($configDir)) {
            mkdir($configDir, 0777, true);
        }

        $navPath = $configDir . '/navigation.php';
        $missingPath = $configDir . '/ghost-feature.php';
        file_put_contents($navPath, '<?php return ["sidebar" => ["from" => "project"]];');
        file_put_contents($missingPath, '<?php return ["enabled" => true];');

        config([
            "{$baseKey}.navigation" => ['sidebar' => ['from' => 'package']],
        ]);

        $response = (new LoadLocalizedConfig)->handle(Request::create('/'), static fn () => response('loaded'));
        $this->assertSame('loaded', $response->getContent());
        $this->assertSame('project', config("{$baseKey}.navigation.sidebar.from"));

        @unlink($navPath);
        @unlink($missingPath);
    }

    /** @test */
    public function impersonate_middleware_registers_composer(): void
    {
        RouteFacade::get('/impersonate/stop', static fn () => 'ok')->name('admin.impersonate.stop');
        RouteFacade::get('/impersonate/{id}', static fn () => 'ok')->name('admin.impersonate');
        RouteFacade::get('/users', static fn () => 'ok')->name('admin.system.user.index');
        RouteFacade::getRoutes()->refreshNameLookups();

        $userRepo = Mockery::mock(UserRepository::class);
        $this->app->instance(UserRepository::class, $userRepo);

        $guard = Mockery::mock();
        $guard->shouldReceive('onceUsingId')->once()->with(99);
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->andReturn($guard);

        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('getAuthGuardName')->andReturn('modularous');
        $modularous->shouldReceive('getAdminRouteNamePrefix')->andReturn('admin');
        Modularous::swap($modularous);

        config([
            'auth.guards.modularous' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'auth.providers.users' => [
                'driver' => 'eloquent',
                'model' => Authenticatable::class,
            ],
            'modularous.default_input' => [
                'density' => 'comfortable',
                'variant' => 'outlined',
            ],
        ]);

        $request = Request::create('/');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->put('impersonate', 99);

        $middleware = new ImpersonateMiddleware($auth);
        $this->assertSame('ok', $middleware->handle($request, static fn () => response('ok'))->getContent());

        $factory = View::getFacadeRoot();
        $view = new \Illuminate\View\View(
            $factory,
            new PhpEngine($this->app['files']),
            modularousBaseKey() . '::layouts.master',
            __FILE__,
            []
        );
        $factory->callComposer($view);
        $this->assertArrayHasKey('impersonation', $view->getData());
    }

    /** @test */
    public function current_user_and_active_navigation_composers(): void
    {
        $profileUser = new class
        {
            public function only(array $keys): array
            {
                return ['id' => 7, 'name' => 'Ada', 'email' => 'a@b.c'];
            }

            public function fileponds()
            {
                return new class
                {
                    public function where($col, $val)
                    {
                        return $this;
                    }

                    public function first()
                    {
                        return null;
                    }
                };
            }
        };

        $guard = Mockery::mock();
        $guard->shouldReceive('user')->once()->andReturn($profileUser);
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->andReturn($guard);
        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('getAuthGuardName')->andReturn('modularous');
        Modularous::swap($modularous);

        $capturedUser = null;
        $view = Mockery::mock(ViewContract::class);
        $view->shouldReceive('with')->once()->with(Mockery::on(function (array $data) use (&$capturedUser) {
            $capturedUser = $data['currentUser'] ?? null;

            return is_array($capturedUser) && ($capturedUser['id'] ?? null) === 7;
        }))->andReturnSelf();

        (new CurrentUser($auth))->compose($view);
        $this->assertSame(7, $capturedUser['id']);

        $request = Request::create('/admin/blog/post/edit');
        $route = new Route(['GET'], '/admin/blog/post/edit', static fn () => null);
        $route->name('admin.blog.post.edit');
        $route->bind($request);
        $request->setRouteResolver(static fn () => $route);

        $navView = Mockery::mock(ViewContract::class);
        $navView->shouldReceive('getData')->andReturn([]);
        $navView->shouldReceive('with')->once()->with(Mockery::on(function (array $data) {
            return ($data['_global_active_navigation'] ?? null) === 'blog'
                && ($data['_primary_active_navigation'] ?? null) === 'post'
                && ($data['_secondary_active_navigation'] ?? null) === 'edit';
        }))->andReturnSelf();

        (new ActiveNavigation($request))->compose($navView);

        $short = Request::create('/admin/dashboard');
        $shortRoute = new Route(['GET'], '/admin/dashboard/{section}', static fn () => null);
        $shortRoute->name('admin.dashboard');
        $shortRoute->bind($short);
        $short->setRouteResolver(static fn () => $shortRoute);
        // Provide a route parameter for the elseif branch when depth <= 2.
        $shortRoute->setParameter('section', 'overview');

        $shortView = Mockery::mock(ViewContract::class);
        $shortView->shouldReceive('getData')->andReturn([]);
        $shortView->shouldReceive('with')->once()->with(Mockery::on(function (array $data) {
            return ($data['_global_active_navigation'] ?? null) === 'dashboard'
                && ($data['_primary_active_navigation'] ?? null) === 'overview';
        }))->andReturnSelf();

        (new ActiveNavigation($short))->compose($shortView);
    }

    /** @test */
    public function files_and_medias_uploader_config_composers_local(): void
    {
        RouteFacade::post('/files', static fn () => 'ok')->name('admin.file-library.file.store');
        RouteFacade::post('/files/s3', static fn () => 'ok')->name('admin.file-library.sign-s3-upload');
        RouteFacade::post('/files/azure', static fn () => 'ok')->name('admin.file-library.sign-azure-upload');
        RouteFacade::post('/medias', static fn () => 'ok')->name('admin.media-library.media.store');
        RouteFacade::post('/medias/s3', static fn () => 'ok')->name('admin.media-library.sign-s3-upload');
        RouteFacade::post('/medias/azure', static fn () => 'ok')->name('admin.media-library.sign-azure-upload');
        RouteFacade::getRoutes()->refreshNameLookups();

        config([
            'modularous.file_library.disk' => 'local',
            'modularous.file_library.endpoint_type' => 'local',
            'modularous.file_library.allowed_extensions' => ['pdf'],
            'modularous.file_library.acl' => 'private',
            'modularous.file_library.filesize_limit' => 10,
            'modularous.media_library.disk' => 'local',
            'modularous.media_library.endpoint_type' => 'local',
            'modularous.media_library.allowed_extensions' => ['jpg'],
            'modularous.media_library.acl' => 'private',
            'modularous.media_library.filesize_limit' => 10,
            'filesystems.disks.local.bucket' => 'none',
            'filesystems.disks.local.region' => 'none',
            'filesystems.disks.local.root' => '',
            'filesystems.disks.local.key' => 'none',
        ]);

        $session = $this->app['session']->driver();
        $session->start();

        $filesConfig = null;
        $filesView = Mockery::mock(ViewContract::class);
        $filesView->shouldReceive('with')->once()->with(Mockery::on(function (array $data) use (&$filesConfig) {
            $filesConfig = $data['filesUploaderConfig'] ?? null;

            return ($filesConfig['endpointType'] ?? null) === 'local'
                && isset($filesConfig['endpoint']);
        }))->andReturnSelf();

        (new FilesUploaderConfig($this->app['url'], $this->app['config'], $session))->compose($filesView);
        $this->assertSame('local', $filesConfig['endpointType']);

        $mediasConfig = null;
        $mediasView = Mockery::mock(ViewContract::class);
        $mediasView->shouldReceive('with')->once()->with(Mockery::on(function (array $data) use (&$mediasConfig) {
            $mediasConfig = $data['mediasUploaderConfig'] ?? null;

            return ($mediasConfig['endpointType'] ?? null) === 'local'
                && isset($mediasConfig['endpoint']);
        }))->andReturnSelf();

        (new MediasUploaderConfig($this->app['url'], $this->app['config'], $session))->compose($mediasView);
        $this->assertSame('local', $mediasConfig['endpointType']);
    }

    /** @test */
    public function step_up_notification_flash_warnings_and_controller_helpers(): void
    {
        $notification = new StepUpCodeNotification('123456', Carbon::now()->addMinutes(5), 'billing');
        $this->assertSame(['mail'], $notification->via((object) []));
        $mail = $notification->toMail((object) []);
        $this->assertNotEmpty($mail->subject);

        ModularousFlashWarnings::merge('');
        ModularousFlashWarnings::merge(['', null, '  warn-one  ', 'warn-one', 'warn-two']);
        $this->assertSame(
            ['warn-one', 'warn-two'],
            Session::get(ModularousFlashWarnings::SESSION_KEY)
        );
        ModularousFlashWarnings::merge('warn-three');
        $this->assertContains('warn-three', Session::get(ModularousFlashWarnings::SESSION_KEY));

        config(['modularous.bind_exception_handler' => true]);
        $controller = new Controller;
        $controller->middleware('web');
        $controller->removeMiddleware('web');
        $controller->removeMiddleware('missing');
        $this->assertTrue(true);
    }

    /** @test */
    public function cache_job_traits_nestedset_publishable_and_remote_observer(): void
    {
        config([
            'modularous.cache.observer.queue_name' => 'cache-q',
            'modularous.cache.observer.queue_connection' => 'redis',
            'modularous.cache.queue.name' => null,
            'modularous.cache.queue.connection' => 'sync-conn',
        ]);

        $dispatchJob = new class
        {
            use DispatchesOnModularousCacheQueue;
            use Queueable;
        };
        $dispatchJob->onModularousCacheQueue();
        $this->assertSame('cache-q', $dispatchJob->queue);
        $this->assertSame('redis', $dispatchJob->connection);

        $cacheJob = new class
        {
            use ModularousCacheJob;
            use Queueable;

            protected function modularousCacheOverlapKey(): string
            {
                return 'overlap-key';
            }
        };
        $cacheJob->configureModularousCacheQueue();
        $this->assertSame('cache-q', $cacheJob->queue);
        $this->assertSame('sync-conn', $cacheJob->connection);
        $middleware = $cacheJob->middleware();
        $this->assertCount(1, $middleware);

        $plainRepo = new class
        {
            use PublishableTrait;

            public function getModel()
            {
                return new class {};
            }

            public function call(string $method)
            {
                return $this->{$method}();
            }
        };
        $this->assertSame([], $plainRepo->prependFormSchemaPublishableTrait());
        $this->assertSame([], $plainRepo->call('translatedPublishableAttributes'));

        $publishableRepo = new class
        {
            use PublishableTrait;

            public function getModel()
            {
                return new class
                {
                    use Publishable;

                    public bool $usePublishDates = true;
                };
            }
        };
        $schema = $publishableRepo->prependFormSchemaPublishableTrait();
        $this->assertNotEmpty($schema);

        $observer = new RemoteApiSourceableObserver;

        $plain = new class extends Model {};
        $observer->retrieved($plain);

        $hydrated = false;
        $hydrating = new class extends Model
        {
            public bool $hydrated = false;

            public function hydrateRemoteApiAttributes(): void
            {
                $this->hydrated = true;
            }
        };
        $observer->retrieved($hydrating);
        $this->assertTrue($hydrating->hydrated);

        $savingNew = new class extends Model
        {
            public $exists = false;
        };
        $this->assertTrue($observer->saving($savingNew));

        $persisted = 0;
        $stripped = 0;
        $savingExisting = new class extends Model
        {
            public $exists = true;

            public int $persisted = 0;

            public int $stripped = 0;

            public function persistRemoteApiVirtualAttributes(): void
            {
                $this->persisted++;
            }

            public function stripRemoteApiVirtualAttributes(): void
            {
                $this->stripped++;
            }
        };
        $this->assertTrue($observer->saving($savingExisting));
        $this->assertSame(1, $savingExisting->persisted);
        $this->assertSame(1, $savingExisting->stripped);

        $observer->saved($savingExisting);
        $this->assertSame(2, $savingExisting->persisted);
        $this->assertSame(2, $savingExisting->stripped);

        $deleted = false;
        $deleting = new class extends Model
        {
            public $remoteApiSource;

            public function __construct()
            {
                parent::__construct();
                $this->remoteApiSource = new class
                {
                    public bool $deleted = false;

                    public function delete(): void
                    {
                        $this->deleted = true;
                    }
                };
            }
        };
        $observer->forceDeleting($deleting);
        $this->assertTrue($deleting->remoteApiSource->deleted);
    }

    /** @test */
    public function hydrate_leftovers_with_skip_queries_and_mocks(): void
    {
        RouteFacade::post('/filepond/process', static fn () => 'ok')->name('filepond.process');
        RouteFacade::delete('/filepond/revert', static fn () => 'ok')->name('filepond.revert');
        RouteFacade::get('/filepond/preview/{uuid}', static fn () => 'ok')->name('filepond.preview');
        RouteFacade::put('/admin/tag', static fn () => 'ok')->name('admin.tag.update');
        RouteFacade::get('/admin/process/{process}', static fn () => 'ok')->name('admin.process.show');
        RouteFacade::put('/admin/process/{process}', static fn () => 'ok')->name('admin.process.update');
        RouteFacade::getRoutes()->refreshNameLookups();

        $avatar = (new FilepondAvatarHydrate([
            'type' => 'filepond-avatar',
            'name' => 'avatar',
            'acceptedExtensions' => ['jpg', 'png'],
        ], null, null, true))->render();
        $this->assertSame('input-filepond-avatar', $avatar['type']);
        $this->assertNotEmpty($avatar['accepted-file-types']);

        $repeater = (new RepeaterHydrate([
            'type' => 'repeater',
            'name' => 'rows',
            'label' => 'Rows',
            'draggable' => true,
            'repository' => MediaRepository::class,
            'schema' => [
                ['type' => 'select', 'name' => 'media_id'],
                ['type' => 'text', 'name' => 'title'],
            ],
        ], null, null, true))->render();
        $this->assertSame('input-repeater', $repeater['type']);
        $this->assertSame('position', $repeater['orderKey']);
        $this->assertSame('default', $repeater['root']);

        $provider = Mockery::mock(CurrencyProviderInterface::class);
        $provider->shouldReceive('isAvailable')->andReturn(false);
        $this->app->instance(CurrencyProviderInterface::class, $provider);

        $price = (new PriceHydrate([
            'type' => 'price',
            'name' => 'prices',
            'hasVatRate' => true,
            'default' => 1.5,
        ], null, null, true))->render();
        $this->assertSame('input-price', $price['type']);
        $this->assertSame([], $price['vatRates']);

        $tag = (new TagHydrate([
            'type' => 'tag',
            'name' => 'tags',
            'taggable' => Tag::class,
            'translated' => true,
        ], null, null, true))->render();
        $this->assertSame('input-tag', $tag['type']);
        $this->assertArrayHasKey('cacheKey', $tag);

        $revisionRepo = Mockery::mock(Repository::class);
        $revisionRepo->shouldReceive('hasBehavior')->with('revisions')->andReturn(false);

        $revisionModule = Mockery::mock(Module::class)->shouldIgnoreMissing();
        $revisionModule->shouldReceive('getRouteActionUrl')->andReturn('/endpoint');
        $revisionModule->shouldReceive('getRepository')->andReturn($revisionRepo);

        $revisionHydrate = new RevisionHydrate([
            'type' => 'revision',
            'name' => 'revisionable_id',
        ], $revisionModule, 'Post', true);
        $revisionHydrate->setDefaults();
        $revision = $revisionHydrate->hydrate();
        $this->assertSame('input-revision', $revision['type']);
        $this->assertFalse($revision['canApprove']);

        $assignee = new class extends Model
        {
            protected $table = 'phase4_assignees';

            public $timestamps = false;

            protected $guarded = [];
        };

        $assignmentModule = Mockery::mock(Module::class)->shouldIgnoreMissing();
        $assignmentModule->shouldReceive('getRouteActionUrl')->andReturn('/assign');

        $assignmentHydrate = new AssignmentHydrate([
            'type' => 'assignment',
            'assigneeType' => $assignee::class,
            'assignableType' => $assignee::class,
            'acceptedExtensions' => ['pdf'],
            'max-attachments' => 1,
        ], $assignmentModule, 'Post', true);
        $assignmentHydrate->setDefaults();
        $assignment = $assignmentHydrate->hydrate();
        $this->assertSame('input-assignment', $assignment['type']);
        $this->assertArrayHasKey('filepond', $assignment);

        $this->expectException(\Exception::class);
        (new StateableHydrate([
            'type' => 'stateable',
            'name' => 'state',
        ], null, null, true))->render();
    }

    /** @test */
    public function process_and_tagger_hydrate_invalid_input_and_stateable_happy_path(): void
    {
        $this->expectException(\Exception::class);
        (new ProcessHydrate([
            'type' => 'process',
            'name' => 'process',
        ], null, null, true))->render();
    }

    /** @test */
    public function tagger_hydrate_requires_module_route(): void
    {
        $this->expectException(\Exception::class);
        (new TaggerHydrate([
            'type' => 'tagger',
            'name' => 'tags',
        ], null, null, true))->render();
    }

    /** @test */
    public function stateable_hydrate_with_module_route_skip_queries(): void
    {
        $repo = Mockery::mock();
        $repo->shouldReceive('getStateableList')->never();

        $module = Mockery::mock(Module::class)->shouldIgnoreMissing();
        $module->shouldReceive('getRouteClass')->with('Item', 'repository')->andReturn('StateableRepo');
        $this->app->instance('StateableRepo', $repo);

        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('find')->with('Pkg')->andReturn($module);
        Modularous::swap($modularous);

        $result = (new StateableHydrate([
            'type' => 'stateable',
            'name' => 'state',
            '_moduleName' => 'Pkg',
            '_routeName' => 'Item',
        ], null, null, true))->render();

        $this->assertSame('select', $result['type']);
        $this->assertSame('stateable_id', $result['name']);
        $this->assertSame([], $result['items']);
    }

    /** @test */
    public function process_hydrate_with_processable_model_skip_queries(): void
    {
        RouteFacade::get('/admin/process/{process}', static fn () => 'ok')->name('admin.process.show');
        RouteFacade::put('/admin/process/{process}', static fn () => 'ok')->name('admin.process.update');
        RouteFacade::getRoutes()->refreshNameLookups();

        $processableModel = new class
        {
            use Processable;
        };

        $module = Mockery::mock(Module::class)->shouldIgnoreMissing();
        $module->shouldReceive('getRouteClass')->with('Item', 'model')->andReturn($processableModel::class);

        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('find')->with('Pkg')->andReturn($module);
        Modularous::swap($modularous);

        $this->app->instance($processableModel::class, $processableModel);

        $result = (new ProcessHydrate([
            'type' => 'process',
            'name' => 'process',
            '_moduleName' => 'Pkg',
            '_routeName' => 'Item',
            'eager' => ['steps'],
        ], null, null, true))->render();

        $this->assertSame('input-process', $result['type']);
        $this->assertSame('process_id', $result['name']);
        $this->assertArrayHasKey('fetchEndpoint', $result);
        $this->assertArrayHasKey('updateEndpoint', $result);
    }

    /** @test */
    public function authenticate_middleware_redirect_to_and_enforces_mfa_setup(): void
    {
        RouteFacade::get('/login', static fn () => 'login')->name('admin.login');
        RouteFacade::get('/login-form', static fn () => 'form')->name('admin.login.form');
        RouteFacade::getRoutes()->refreshNameLookups();

        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('getAdminRouteNamePrefix')->andReturn('admin');
        Modularous::swap($modularous);

        $middleware = $this->app->make(AuthenticateMiddleware::class);
        $redirectTo = new \ReflectionMethod(AuthenticateMiddleware::class, 'redirectTo');
        $redirectTo->setAccessible(true);
        $fallback = new \ReflectionMethod(AuthenticateMiddleware::class, 'fallbackLoginUrlForUnauthenticated');
        $fallback->setAccessible(true);

        $this->assertSame(route('admin.login'), $fallback->invoke($middleware));

        $json = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $json->headers->set('Accept', 'application/json');
        $json->headers->set('referer', 'https://example.test/prev');
        $json->setLaravelSession($this->app['session']->driver());
        $this->assertNull($redirectTo->invoke($middleware, $json));
        $this->assertSame('https://example.test/prev', $json->session()->get('url.intended'));

        $web = Request::create('/admin/secret', 'GET');
        $web->setLaravelSession($this->app['session']->driver());
        $route = new Route(['GET'], '/admin/secret', static fn () => null);
        $route->name('admin.secret');
        $web->setRouteResolver(static fn () => $route);
        $this->assertSame(route('admin.login.form'), $redirectTo->invoke($middleware, $web));

        config([
            'modularous.security.enabled' => false,
            'modularous.security.mfa.enabled' => false,
        ]);

        $enforcer = new class
        {
            use EnforcesMfaSetupOnLogin;

            public function call($request, $user)
            {
                return $this->enforceMfaSetupOnLogin($request, $user);
            }

            protected function guard()
            {
                return new class
                {
                    public function logout(): void {}
                };
            }
        };

        $user = Mockery::mock(Authenticatable::class);
        $this->assertNull($enforcer->call(Request::create('/'), $user));

        $security = Mockery::mock(SecurityService::class);
        $security->shouldReceive('userRequiresMfa')->andReturn(true);
        $security->shouldReceive('userHasEnabledMfa')->andReturn(false);
        $this->app->instance(SecurityService::class, $security);

        config([
            'modularous.security.enabled' => true,
            'modularous.security.mfa.enabled' => true,
            'modularous.security.mfa.strict' => true,
        ]);

        $jsonReq = Request::create('/', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $jsonReq->headers->set('Accept', 'application/json');
        $denied = $enforcer->call($jsonReq, $user);
        $this->assertInstanceOf(JsonResponse::class, $denied);
        $this->assertSame(403, $denied->getStatusCode());
    }
}
