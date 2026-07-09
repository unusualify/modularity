<?php

namespace Unusualify\Modularous\Tests\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Http\Middleware\VerifyModularousCacheWebhook;
use Unusualify\Modularous\Tests\TestCase;

class VerifyModularousCacheWebhookTest extends TestCase
{
    public function test_aborts_when_webhook_disabled(): void
    {
        Config::set('modularous.cache.webhook.enabled', false);

        $middleware = new VerifyModularousCacheWebhook;

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $middleware->handle(Request::create('/hook', 'POST'), fn () => response('ok'));
    }

    public function test_aborts_when_secret_missing(): void
    {
        Config::set('modularous.cache.webhook.enabled', true);
        Config::set('modularous.cache.webhook.secret', null);

        $middleware = new VerifyModularousCacheWebhook;

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle(Request::create('/hook', 'POST'), fn () => response('ok'));
    }

    public function test_passes_request_with_valid_signature(): void
    {
        Config::set('modularous.cache.webhook.enabled', true);
        Config::set('modularous.cache.webhook.secret', 'unit-secret');

        $payload = '{"module":"Blog"}';
        $request = Request::create('/hook', 'POST', [], [], [], [], $payload);
        $request->headers->set(
            'X-Modularous-Signature',
            'sha256=' . hash_hmac('sha256', $payload, 'unit-secret')
        );

        $middleware = new VerifyModularousCacheWebhook;
        $response = $middleware->handle($request, fn () => response('accepted', 202));

        $this->assertSame(202, $response->getStatusCode());
    }

    public function test_aborts_on_signature_mismatch(): void
    {
        Config::set('modularous.cache.webhook.enabled', true);
        Config::set('modularous.cache.webhook.secret', 'unit-secret');

        $payload = '{"module":"Blog"}';
        $request = Request::create('/hook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Modularous-Signature', 'sha256=deadbeef');

        $middleware = new VerifyModularousCacheWebhook;

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Signature mismatch.');

        $middleware->handle($request, fn () => response('accepted', 202));
    }
}
