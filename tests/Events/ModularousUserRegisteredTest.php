<?php

namespace Unusualify\Modularous\Tests\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Events\ModularousUserRegistered;
use Unusualify\Modularous\Tests\TestCase;

class ModularousUserRegisteredTest extends TestCase
{
    protected function makeUser(): Authenticatable
    {
        return new class implements Authenticatable
        {
            public function getAuthIdentifierName()
            {
                return 'id';
            }

            public function getAuthIdentifier()
            {
                return 1;
            }

            public function getAuthPasswordName()
            {
                return 'password';
            }

            public function getAuthPassword()
            {
                return 'secret';
            }

            public function getRememberToken()
            {
                return null;
            }

            public function setRememberToken($value)
            {
                //
            }

            public function getRememberTokenName()
            {
                return 'remember_token';
            }
        };
    }

    public function test_constructor_sets_user_and_request()
    {
        $user = $this->makeUser();
        $request = Request::create('/register', 'POST');

        $event = new ModularousUserRegistered($user, $request);

        $this->assertSame($user, $event->user);
        $this->assertSame($request, $event->request);
    }

    public function test_is_oauth_defaults_to_false()
    {
        $event = new ModularousUserRegistered($this->makeUser(), Request::create('/register', 'POST'));

        $this->assertFalse($event->isOauth());
    }

    public function test_is_oauth_returns_true_when_set()
    {
        $event = new ModularousUserRegistered($this->makeUser(), Request::create('/register', 'POST'), true);

        $this->assertTrue($event->isOauth());
    }

    public function test_is_oauth_can_be_explicitly_false()
    {
        $event = new ModularousUserRegistered($this->makeUser(), Request::create('/register', 'POST'), false);

        $this->assertFalse($event->isOauth());
    }

    public function test_user_and_request_properties_are_public()
    {
        $user = $this->makeUser();
        $request = Request::create('/register', 'POST');

        $event = new ModularousUserRegistered($user, $request, true);

        $this->assertEquals($user->getAuthIdentifier(), $event->user->getAuthIdentifier());
        $this->assertEquals('/register', $event->request->getPathInfo());
        $this->assertEquals('POST', $event->request->getMethod());
    }

    public function test_event_instances_are_independent()
    {
        $userOne = $this->makeUser();
        $userTwo = $this->makeUser();

        $eventOne = new ModularousUserRegistered($userOne, Request::create('/register', 'POST'), true);
        $eventTwo = new ModularousUserRegistered($userTwo, Request::create('/oauth/callback', 'GET'), false);

        $this->assertSame($userOne, $eventOne->user);
        $this->assertSame($userTwo, $eventTwo->user);
        $this->assertTrue($eventOne->isOauth());
        $this->assertFalse($eventTwo->isOauth());
        $this->assertEquals('/register', $eventOne->request->getPathInfo());
        $this->assertEquals('/oauth/callback', $eventTwo->request->getPathInfo());
    }

    public function test_event_uses_serializes_models_trait()
    {
        $traits = class_uses(ModularousUserRegistered::class);

        $this->assertContains(SerializesModels::class, $traits);
    }
}
