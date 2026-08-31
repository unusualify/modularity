<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\Utilities;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Mockery;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\HandlesOAuth;
use Unusualify\Modularous\Http\Requests\OauthRequest;
use Unusualify\Modularous\Tests\TestCase;

class HandlesOAuthCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function bindSocialiteDriver($driver): void
    {
        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('driver')->andReturn($driver);
        $this->app->instance(Factory::class, $factory);
        Socialite::swap($factory);
    }

    private function makeController(array $overrides = []): object
    {
        $redirector = Mockery::mock(Redirector::class);
        $redirector->shouldReceive('to')->andReturnUsing(fn ($url) => redirect()->to($url));
        $redirector->shouldReceive('intended')->andReturnUsing(fn ($url) => redirect()->to($url));

        $viewFactory = Mockery::mock();
        $viewFactory->shouldReceive('make')->andReturn(response('login-view'));

        $authManager = Mockery::mock();
        $guard = Mockery::mock();
        $guard->shouldReceive('login')->andReturnNull();
        $authManager->shouldReceive('guard')->andReturn($guard);

        return new class($redirector, $viewFactory, $authManager, $overrides)
        {
            use HandlesOAuth;

            public $redirector;

            public $viewFactory;

            public $authManager;

            public string $redirectTo = '/admin';

            public array $overrides;

            public function __construct($redirector, $viewFactory, $authManager, array $overrides)
            {
                $this->redirector = $redirector;
                $this->viewFactory = $viewFactory;
                $this->authManager = $authManager;
                $this->overrides = $overrides;
            }

            public function afterAuthentication($request, $user)
            {
                return response('authenticated:' . ($user->id ?? 'x'));
            }

            public function attemptLogin($request): bool
            {
                return (bool) ($this->overrides['attemptLogin'] ?? false);
            }

            public function authFormTitle($title, $attrs = []): string
            {
                return (string) $title;
            }

            public function authFormBaseAttributes($schema, $action, $button): array
            {
                return ['schema' => $schema, 'actionUrl' => $action, 'buttonText' => $button];
            }

            public function authFormBottomSlots(): array
            {
                return [];
            }

            protected function buildAuthViewData(string $key, array $extra = []): array
            {
                return array_merge(['pageKey' => $key], $extra);
            }

            public function callOauthErrorRedirect(string $title, string $description)
            {
                return $this->oauthErrorRedirect($title, $description);
            }
        };
    }

    /** @test */
    public function redirect_to_provider_delegates_to_socialite(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('redirect')->once()->andReturn(redirect()->to('https://oauth.test'));
        $this->bindSocialiteDriver($driver);

        $response = $this->makeController()->redirectToProvider('google');
        $this->assertTrue($response->isRedirect());
    }

    /** @test */
    public function handle_provider_callback_error_paths(): void
    {
        Route::get('/login', fn () => 'login')->name('admin.login.form');

        $clientException = new ClientException(
            'cancelled',
            new GuzzleRequest('GET', 'https://oauth'),
            new GuzzleResponse(400)
        );

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andThrow($clientException);
        $this->bindSocialiteDriver($driver);
        $cancelled = $this->makeController()->handleProviderCallback('google', OauthRequest::create('/'));
        $this->assertTrue($cancelled->isRedirect());

        $driver2 = Mockery::mock();
        $driver2->shouldReceive('user')->once()->andThrow(new InvalidStateException('bad'));
        $this->bindSocialiteDriver($driver2);
        $invalid = $this->makeController()->handleProviderCallback('google', OauthRequest::create('/'));
        $this->assertTrue($invalid->isRedirect());

        $driver3 = Mockery::mock();
        $driver3->shouldReceive('user')->once()->andThrow(new \RuntimeException('boom'));
        $this->bindSocialiteDriver($driver3);
        $general = $this->makeController()->handleProviderCallback('google', OauthRequest::create('/'));
        $this->assertTrue($general->isRedirect());
    }

    /** @test */
    public function handle_provider_callback_linked_user_and_create_user_paths(): void
    {
        $oauthUser = (object) [
            'email' => 'new@example.com',
            'name' => 'New',
            'surname' => null,
            'family_name' => 'User',
            'id' => 'oauth-1',
        ];

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($oauthUser);
        $this->bindSocialiteDriver($driver);

        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('getAuthGuardName')->andReturn('modularous');
        $modularous->shouldReceive('find')->andReturn(null);
        Modularous::swap($modularous);
        Route::get('/oauth/password', fn () => 'pwd')->name('admin.login.oauth.showPasswordForm');
        Route::get('/home', fn () => 'home')->name('admin.home');

        $linkedUser = new class
        {
            public int $id = 7;

            public function linkProvider($oauthUser, $provider): void {}
        };

        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('oauthUser')->andReturn($linkedUser);
        $repository->shouldReceive('oauthIsUserLinked')->andReturn(true);
        $repository->shouldReceive('oauthUpdateProvider')->andReturn($linkedUser);
        App::instance(UserRepository::class, $repository);

        $response = $this->makeController()->handleProviderCallback('google', OauthRequest::create('/'));
        $this->assertSame('authenticated:7', $response->getContent());

        $unlinkedWithPassword = new class
        {
            public int $id = 8;

            public string $password = 'secret';

            public function linkProvider($oauthUser, $provider): void {}
        };

        $repository2 = Mockery::mock(UserRepository::class);
        $repository2->shouldReceive('oauthUser')->andReturn($unlinkedWithPassword);
        $repository2->shouldReceive('oauthIsUserLinked')->andReturn(false);
        App::instance(UserRepository::class, $repository2);

        $request = OauthRequest::create('/');
        $request->setLaravelSession($this->app['session']->driver());
        $passwordForm = $this->makeController()->handleProviderCallback('google', $request);
        $this->assertTrue($passwordForm->isRedirect());
        $this->assertSame(8, $request->session()->get('oauth:user_id'));

        $unlinkedNoPassword = new class
        {
            public int $id = 9;

            public $password = null;

            public function linkProvider($oauthUser, $provider): void {}
        };

        $repository3 = Mockery::mock(UserRepository::class);
        $repository3->shouldReceive('oauthUser')->andReturn($unlinkedNoPassword);
        $repository3->shouldReceive('oauthIsUserLinked')->andReturn(false);
        App::instance(UserRepository::class, $repository3);

        $response = $this->makeController()->handleProviderCallback('google', OauthRequest::create('/'));
        $this->assertSame('authenticated:9', $response->getContent());

        // oauthCreateUser registration path needs a fully initialized OauthRequest + events;
        // covered linked / password-redirect / link-without-password paths above.
    }

    /** @test */
    public function link_provider_failure_and_oauth_error_redirect(): void
    {
        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('getAuthGuardName')->andReturn('modularous');
        Modularous::swap($modularous);
        Route::get('/login', fn () => 'login')->name('admin.login.form');

        // linkProvider success needs User::findOrFail — skipped (DB-coupled).
        try {
            $this->makeController(['attemptLogin' => false])->linkProvider(Request::create('/', 'POST'));
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
        }

        $redirect = $this->makeController()->callOauthErrorRedirect('T', 'D');
        $this->assertTrue($redirect->isRedirect());
    }
}
