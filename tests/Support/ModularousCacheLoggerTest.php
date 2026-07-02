<?php

namespace Unusualify\Modularous\Tests\Support;

use Illuminate\Support\Facades\Log;
use Mockery;
use Unusualify\Modularous\Support\ModularousCacheLogger;
use Unusualify\Modularous\Tests\TestCase;

class ModularousCacheLoggerTest extends TestCase
{
    protected function tearDown(): void
    {
        ModularousCacheLogger::clearChannelOverride();
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_logs_to_configured_channel_without_throwing(): void
    {
        $this->app['config']->set('modularous.cache.logging.enabled', true);
        $this->app['config']->set('modularous.cache.logging.channel', 'modularous-resource-cache');
        $this->app['config']->set('logging.channels.modularous-resource-cache', [
            'driver' => 'daily',
            'path' => storage_path('logs/modularous-resource-cache.log'),
            'level' => 'info',
            'days' => 10,
        ]);

        $channelLogger = Mockery::mock();
        $channelLogger->shouldReceive('info')
            ->once()
            ->with('cache.test.event', ['foo' => 'bar']);

        Log::shouldReceive('channel')
            ->once()
            ->with('modularous-resource-cache')
            ->andReturn($channelLogger);

        ModularousCacheLogger::info('cache.test.event', ['foo' => 'bar']);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_does_not_log_when_explicitly_disabled(): void
    {
        $this->app['config']->set('modularous.cache.logging.enabled', false);
        $this->app['config']->set('modularous.cache.logging.channel', 'modularous-resource-cache');
        $this->app['config']->set('logging.channels.modularous-resource-cache', [
            'driver' => 'daily',
            'path' => storage_path('logs/modularous-resource-cache.log'),
            'level' => 'info',
            'days' => 10,
        ]);

        Log::shouldReceive('channel')->never();

        ModularousCacheLogger::info('cache.test.disabled');

        $this->assertTrue(true);
    }

    /** @test */
    public function it_does_not_log_when_channel_is_missing(): void
    {
        $this->app['config']->set('modularous.cache.logging.enabled', true);
        $this->app['config']->set('modularous.cache.logging.channel', 'missing-channel');

        Log::shouldReceive('channel')->never();

        ModularousCacheLogger::info('cache.test.missing_channel');

        $this->assertTrue(true);
    }

    /** @test */
    public function it_honors_channel_override_from_artisan_option(): void
    {
        $this->app['config']->set('modularous.cache.logging.enabled', true);
        $this->app['config']->set('modularous.cache.logging.channel', 'modularous-resource-cache');
        $this->app['config']->set('logging.channels.modularous-resource-cache', [
            'driver' => 'daily',
            'path' => storage_path('logs/modularous-resource-cache.log'),
            'level' => 'info',
            'days' => 10,
        ]);
        $this->app['config']->set('logging.channels.custom-cache-log', [
            'driver' => 'single',
            'path' => storage_path('logs/custom-cache.log'),
            'level' => 'info',
        ]);

        $channelLogger = Mockery::mock();
        $channelLogger->shouldReceive('info')
            ->once()
            ->with('cache.test.override', []);

        Log::shouldReceive('channel')
            ->once()
            ->with('custom-cache-log')
            ->andReturn($channelLogger);

        ModularousCacheLogger::setChannelOverride('custom-cache-log');
        ModularousCacheLogger::info('cache.test.override');

        $this->assertTrue(true);
    }
}
