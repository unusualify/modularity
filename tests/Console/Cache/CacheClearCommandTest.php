<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console\Cache;

use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Tests\TestCase;

class CacheClearCommandTest extends TestCase
{
    /** @test */
    public function clearing_all_caches_for_a_route_only_invalidates_that_route(): void
    {
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('invalidateModuleRoute')->once()->with('Blog', 'Post')->andReturn(true);
        ModularousCache::shouldReceive('invalidateModule')->never();

        $this->artisan('modularous:cache:clear', ['module' => 'blog', 'routeName' => 'post'])
            ->expectsOutputToContain('All caches cleared for Blog')
            ->assertExitCode(0);
    }

    /** @test */
    public function clearing_all_caches_for_a_module_invalidates_the_whole_module(): void
    {
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('invalidateModule')->once()->with('Blog')->andReturn(true);
        ModularousCache::shouldReceive('invalidateModuleRoute')->never();

        $this->artisan('modularous:cache:clear', ['module' => 'blog'])
            ->expectsOutputToContain('All caches cleared for Blog')
            ->assertExitCode(0);
    }
}
