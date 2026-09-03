<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Unusualify\Modularous\Services\ModulePresentationAssetLoader;
use Unusualify\Modularous\Tests\TestCase;

class ModulePresentationAssetLoaderTest extends TestCase
{
    /** @test */
    public function it_does_not_invoke_load_callback_when_deferral_is_disabled(): void
    {
        $loader = new ModulePresentationAssetLoader;
        $invoked = false;

        $loader->configure(false, function () use (&$invoked): void {
            $invoked = true;
        });

        $loader->ensureLoaded();

        $this->assertFalse($invoked);
        $this->assertFalse($loader->isDeferred());
    }

    /** @test */
    public function it_invokes_load_callback_once_when_deferred(): void
    {
        $loader = new ModulePresentationAssetLoader;
        $invocations = 0;

        $loader->configure(true, function () use (&$invocations): void {
            $invocations++;
        });

        $this->assertTrue($loader->isDeferred());

        $loader->ensureLoaded();
        $loader->ensureLoaded();

        $this->assertSame(1, $invocations);
    }
}
