<?php

namespace Unusualify\Modularous\Tests\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Events\VerifiedEmailRegister;
use Unusualify\Modularous\Tests\TestCase;

class VerifiedEmailRegisterTest extends TestCase
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

    public function test_constructor_sets_user()
    {
        $user = $this->makeUser();

        $event = new VerifiedEmailRegister($user);

        $this->assertSame($user, $event->user);
    }

    public function test_user_property_is_public()
    {
        $user = $this->makeUser();

        $event = new VerifiedEmailRegister($user);

        $this->assertEquals($user->getAuthIdentifier(), $event->user->getAuthIdentifier());
    }

    public function test_event_instances_are_independent()
    {
        $userOne = $this->makeUser();
        $userTwo = $this->makeUser();

        $eventOne = new VerifiedEmailRegister($userOne);
        $eventTwo = new VerifiedEmailRegister($userTwo);

        $this->assertSame($userOne, $eventOne->user);
        $this->assertSame($userTwo, $eventTwo->user);
        $this->assertNotSame($eventOne->user, $eventTwo->user);
    }

    public function test_event_uses_serializes_models_trait()
    {
        $traits = class_uses(VerifiedEmailRegister::class);

        $this->assertContains(SerializesModels::class, $traits);
    }
}
