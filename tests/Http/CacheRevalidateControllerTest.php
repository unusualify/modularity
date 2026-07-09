<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Jobs\Cache\RevalidatePresentationCacheJob;
use Unusualify\Modularous\Tests\TestCase;

class CacheRevalidateControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.webhook.enabled', true);
        Config::set('modularous.cache.webhook.secret', 'test-webhook-secret');
    }

    /** @test */
    public function it_rejects_requests_without_valid_signature(): void
    {
        $response = $this->postJson('/api/modularous/cache/revalidate', [
            'module' => 'PrimaryPage',
            'route' => 'Home',
            'action' => 'purge',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_queues_revalidate_job_with_valid_hmac_signature(): void
    {
        Bus::fake();

        $payload = json_encode([
            'module' => 'PrimaryPage',
            'route' => 'Home',
            'id' => 5,
            'action' => 'both',
            'types' => ['presentationItem'],
        ], JSON_THROW_ON_ERROR);

        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');

        $response = $this->call(
            'POST',
            '/api/modularous/cache/revalidate',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Modularous-Signature' => $signature,
            ],
            $payload,
        );

        $response->assertOk();
        $response->assertJson([
            'queued' => true,
            'action' => 'both',
            'module' => 'PrimaryPage',
            'route' => 'Home',
        ]);

        Bus::assertDispatched(RevalidatePresentationCacheJob::class);
    }

    /** @test */
    public function it_returns_404_when_webhook_is_disabled(): void
    {
        Config::set('modularous.cache.webhook.enabled', false);

        $response = $this->postJson('/api/modularous/cache/revalidate', [
            'module' => 'PrimaryPage',
            'route' => 'Home',
            'action' => 'purge',
        ]);

        $response->assertStatus(404);
    }
}
