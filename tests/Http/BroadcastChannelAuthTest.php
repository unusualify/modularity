<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\BroadcastManager;
use Unusualify\Modularous\Tests\TestCase;

class BroadcastChannelAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'null',
            'auth.defaults.guard' => 'web',
            'auth.guards.modularous' => [
                'driver' => 'session',
                'provider' => 'modularous_users',
            ],
            'auth.providers.modularous_users' => [
                'driver' => 'eloquent',
                'model' => User::class,
            ],
        ]);

        // Re-register Modularous channels with the test auth config.
        $guard = Modularous::getAuthGuardName();
        $broadcastGuards = ['guards' => [$guard]];

        Broadcast::channel('users.{userId}', function ($user, $userId) {
            if ($user === null) {
                return false;
            }

            return (int) $user->id === (int) $userId;
        }, $broadcastGuards);

        Broadcast::channel('models.{modelId}', function ($user, $modelId) {
            return $user !== null;
        }, $broadcastGuards);

        Broadcast::channel('editing.{modelType}.{modelId}', function ($user, $modelType, $modelId) {
            if ($user === null) {
                return false;
            }

            return [
                'id' => $user->id,
                'name' => trim(($user->name ?? '') . ' ' . ($user->surname ?? '')),
            ];
        }, $broadcastGuards);
    }

    public function test_user_receives_broadcast_notifications_on_users_channel(): void
    {
        $user = new User;
        $user->id = 17;

        $this->assertSame('users.17', $user->receivesBroadcastNotificationsOn());
    }

    public function test_broadcasting_auth_route_is_registered_with_modularous_guard(): void
    {
        $route = collect(Route::getRoutes())->first(
            fn ($route) => $route->uri() === 'broadcasting/auth'
        );

        $this->assertNotNull($route, 'broadcasting/auth route should be registered');
        $this->assertContains('GET', $route->methods());
        $this->assertContains('POST', $route->methods());

        $middleware = $route->gatherMiddleware();
        $this->assertContains('web', $middleware);
        $this->assertContains('modularous.auth:' . Modularous::getAuthGuardName(), $middleware);
    }

    public function test_users_channel_authorization_matches_authenticated_user(): void
    {
        $user = new User;
        $user->id = 5;

        $authorize = function ($authUser, $userId) {
            return (int) $authUser->id === (int) $userId;
        };

        $this->assertTrue($authorize($user, '5'));
        $this->assertFalse($authorize($user, '9'));
    }

    public function test_models_channel_requires_authenticated_user(): void
    {
        $user = new User;
        $user->id = 1;

        $authorize = function ($authUser, $modelId) {
            return $authUser !== null;
        };

        $this->assertTrue($authorize($user, '1'));
        $this->assertFalse($authorize(null, '1'));
    }

    public function test_editing_presence_channel_returns_user_payload(): void
    {
        $user = new User;
        $user->id = 3;
        $user->name = 'Ada';
        $user->surname = 'Lovelace';

        $authorize = function ($authUser, $modelType, $modelId) {
            return [
                'id' => $authUser->id,
                'name' => trim(($authUser->name ?? '') . ' ' . ($authUser->surname ?? '')),
            ];
        };

        $payload = $authorize($user, 'Modules-Blog-Entities-Post', '9');

        $this->assertSame(3, $payload['id']);
        $this->assertSame('Ada Lovelace', $payload['name']);
    }

    public function test_editing_channel_pattern_matches_dash_encoded_model_type(): void
    {
        $encoded = BroadcastManager::encodeModelType('Modules\\Blog\\Entities\\Post');
        $channel = 'editing.' . $encoded . '.9';
        $pattern = 'editing.{modelType}.{modelId}';

        $regex = '/^' . preg_replace('/\{(.*?)\}/', '([^\.]+)', str_replace('.', '\.', $pattern)) . '$/';

        $this->assertSame(1, preg_match($regex, $channel));
        $this->assertSame('Modules-Blog-Entities-Post', $encoded);
    }

    public function test_channel_options_use_modularous_guard_only(): void
    {
        $broadcaster = Broadcast::driver();
        $optionsProperty = new \ReflectionProperty($broadcaster, 'channelOptions');
        $optionsProperty->setAccessible(true);
        $options = $optionsProperty->getValue($broadcaster);

        $this->assertSame(
            [Modularous::getAuthGuardName()],
            $options['users.{userId}']['guards'] ?? null
        );
        $this->assertSame(
            [Modularous::getAuthGuardName()],
            $options['editing.{modelType}.{modelId}']['guards'] ?? null
        );
    }

    public function test_registered_users_channel_callback_denies_other_user_ids(): void
    {
        $user = new User;
        $user->id = 5;

        $callback = Broadcast::driver()->getChannels()['users.{userId}'] ?? null;
        $this->assertIsCallable($callback);

        $this->assertTrue((bool) $callback($user, '5'));
        $this->assertFalse((bool) $callback($user, '999'));
        $this->assertFalse((bool) $callback(null, '5'));
    }

    public function test_registered_editing_channel_callback_returns_presence_payload(): void
    {
        $user = new User;
        $user->id = 5;
        $user->name = 'Test';
        $user->surname = 'User';

        $callback = Broadcast::driver()->getChannels()['editing.{modelType}.{modelId}'] ?? null;
        $this->assertIsCallable($callback);

        $payload = $callback($user, 'Modules-Blog-Entities-Post', '9');

        $this->assertSame(5, $payload['id']);
        $this->assertSame('Test User', $payload['name']);
        $this->assertFalse((bool) $callback(null, 'Modules-Blog-Entities-Post', '9'));
    }
}
