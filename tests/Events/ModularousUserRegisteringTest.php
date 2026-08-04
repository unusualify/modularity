<?php

namespace Unusualify\Modularous\Tests\Events;

use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Events\ModularousUserRegistering;
use Unusualify\Modularous\Tests\TestCase;

class ModularousUserRegisteringTest extends TestCase
{
    public function test_constructor_sets_request()
    {
        $request = Request::create('/register', 'POST');

        $event = new ModularousUserRegistering($request);

        $this->assertSame($request, $event->request);
    }

    public function test_is_oauth_defaults_to_false()
    {
        $event = new ModularousUserRegistering(Request::create('/register', 'POST'));

        $this->assertFalse($event->isOauth());
    }

    public function test_is_oauth_returns_true_when_set()
    {
        $event = new ModularousUserRegistering(Request::create('/register', 'POST'), true);

        $this->assertTrue($event->isOauth());
    }

    public function test_is_oauth_can_be_explicitly_false()
    {
        $event = new ModularousUserRegistering(Request::create('/register', 'POST'), false);

        $this->assertFalse($event->isOauth());
    }

    public function test_request_property_is_public()
    {
        $request = Request::create('/register', 'POST');

        $event = new ModularousUserRegistering($request, true);

        $this->assertEquals('/register', $event->request->getPathInfo());
        $this->assertEquals('POST', $event->request->getMethod());
    }

    public function test_event_instances_are_independent()
    {
        $eventOne = new ModularousUserRegistering(Request::create('/register', 'POST'), true);
        $eventTwo = new ModularousUserRegistering(Request::create('/oauth/callback', 'GET'), false);

        $this->assertTrue($eventOne->isOauth());
        $this->assertFalse($eventTwo->isOauth());
        $this->assertEquals('/register', $eventOne->request->getPathInfo());
        $this->assertEquals('/oauth/callback', $eventTwo->request->getPathInfo());
    }

    public function test_event_uses_serializes_models_trait()
    {
        $traits = class_uses(ModularousUserRegistering::class);

        $this->assertContains(SerializesModels::class, $traits);
    }
}
