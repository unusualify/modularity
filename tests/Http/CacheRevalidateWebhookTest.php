<?php

namespace Unusualify\Modularous\Tests\Http;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Jobs\Cache\RevalidatePresentationCacheJob;
use Unusualify\Modularous\Tests\TestCase;

class CacheRevalidateWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.webhook.enabled', true);
        Config::set('modularous.cache.webhook.secret', 'test-webhook-secret');

        Route::post('/api/modularous/cache/revalidate', \Unusualify\Modularous\Http\Controllers\API\CacheRevalidateController::class)
            ->middleware(['api', 'modularous.cache.webhook']);
    }

    /** @test */
    public function webhook_rejects_requests_without_valid_signature(): void
    {
        $response = $this->postJson('/api/modularous/cache/revalidate', [
            'module' => 'PrimaryPage',
            'route' => 'Home',
            'action' => 'warm',
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function webhook_queues_revalidate_job_with_valid_signature(): void
    {
        Bus::fake();

        $payload = json_encode([
            'module' => 'PrimaryPage',
            'route' => 'Home',
            'id' => 12,
            'action' => 'both',
            'types' => ['presentationItem'],
        ]);

        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');

        $response = $this->call(
            'POST',
            '/api/modularous/cache/revalidate',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_MODULAROUS_SIGNATURE' => $signature,
            ],
            $payload,
        );

        $response->assertOk()
            ->assertJson([
                'queued' => true,
                'action' => 'both',
                'module' => 'PrimaryPage',
                'route' => 'Home',
            ]);

        Bus::assertDispatched(RevalidatePresentationCacheJob::class, function (RevalidatePresentationCacheJob $job) {
            return $job->moduleName === 'PrimaryPage'
                && $job->moduleRouteName === 'Home'
                && $job->recordId === 12
                && $job->action === 'both';
        });
    }

    /** @test */
    public function webhook_returns_not_found_when_disabled(): void
    {
        Config::set('modularous.cache.webhook.enabled', false);

        $payload = json_encode([
            'module' => 'PrimaryPage',
            'route' => 'Home',
            'action' => 'purge',
        ]);

        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');

        $response = $this->call(
            'POST',
            '/api/modularous/cache/revalidate',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_MODULAROUS_SIGNATURE' => $signature,
            ],
            $payload,
        );

        $response->assertNotFound();
    }
}
