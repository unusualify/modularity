<?php

namespace Unusualify\Modularous\Tests\Events;

use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Events\ModularousUserVerification;
use Unusualify\Modularous\Tests\TestCase;

class ModularousUserVerificationTest extends TestCase
{
    public function test_constructor_sets_request()
    {
        $request = Request::create('/verify', 'GET');

        $event = new ModularousUserVerification($request);

        $this->assertSame($request, $event->request);
    }

    public function test_request_property_is_public()
    {
        $request = Request::create('/verify', 'GET');

        $event = new ModularousUserVerification($request);

        $this->assertEquals('/verify', $event->request->getPathInfo());
        $this->assertEquals('GET', $event->request->getMethod());
    }

    public function test_event_instances_are_independent()
    {
        $eventOne = new ModularousUserVerification(Request::create('/verify', 'GET'));
        $eventTwo = new ModularousUserVerification(Request::create('/verify/confirm', 'POST'));

        $this->assertEquals('/verify', $eventOne->request->getPathInfo());
        $this->assertEquals('/verify/confirm', $eventTwo->request->getPathInfo());
        $this->assertNotSame($eventOne->request, $eventTwo->request);
    }

    public function test_event_uses_serializes_models_trait()
    {
        $traits = class_uses(ModularousUserVerification::class);

        $this->assertContains(SerializesModels::class, $traits);
    }
}
