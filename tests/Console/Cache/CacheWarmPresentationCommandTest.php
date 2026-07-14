<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console\Cache;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use TestModules\TestModule\Entities\Item;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Jobs\Cache\WarmModuleRouteCachesJob;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestCase;

class CacheWarmPresentationCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $fixturesPath = IsolatedTestModules::path();
        IsolatedTestModules::seedRoutesStatuses();
        $app['config']->set('modules.paths.modules', $fixturesPath);
        $app['config']->set('modules.scan.paths', [$fixturesPath]);
        $app['config']->set('modules.namespace', 'TestModules');
        $app['config']->set('modules.paths.generator.model', [
            'path' => 'Entities',
            'namespace' => 'Entities',
            'generate' => false,
        ]);

        Modularous::boot();
    }

    protected function setUp(): void
    {
        parent::setUp();

        MockModuleManager::initialize();
        IsolatedTestModules::seedRoutesStatuses(['TestModule' => ['Item' => true]]);

        Schema::dropIfExists('test_module_items');
        Schema::create('test_module_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.all_modules', true);
        Config::set('modularous.cache.presentationItem.store', 'url');
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.presentationItem', true);

        $this->app->forgetInstance('modularous.cache');

        Item::query()->create(['name' => 'Alpha']);
        Item::query()->create(['name' => 'Beta']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_module_items');

        parent::tearDown();
    }

    /** @test */
    public function dry_run_prints_summary_after_queue_mode_preview(): void
    {
        Bus::fake();
        Config::set('queue.default', 'database');
        Config::set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);

        $exitCode = Artisan::call('modularous:cache:warm-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--dry-run' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Dry run complete. Would process 2 record(s)', $output, $output);
    }

    /** @test */
    public function dry_run_lists_route_job_without_dispatching(): void
    {
        Bus::fake();
        Config::set('queue.default', 'database');
        Config::set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);

        $this->artisan('modularous:cache:warm-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--dry-run' => true,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Dry run — listing targets that would be warmed.')
            ->expectsOutputToContain('Would queue WarmModuleRouteCachesJob: TestModule::Item (2 records)')
            ->expectsOutputToContain('[queue, locale=all]');

        Bus::assertNotDispatched(WarmModuleRouteCachesJob::class);
        Bus::assertNotDispatched(WarmPresentationItemJob::class);
    }

    /** @test */
    public function dry_run_sync_mode_lists_warmup_without_calling_service(): void
    {
        Bus::fake();

        $this->artisan('modularous:cache:warm-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--sync' => true,
            '--dry-run' => true,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Would warmup presentationItem: TestModule::Item (2 records)')
            ->expectsOutputToContain('[sync, locale=all]');

        Bus::assertNothingDispatched();
    }

    /** @test */
    public function dry_run_with_id_and_locale_filters_output(): void
    {
        Bus::fake();
        Config::set('queue.default', 'database');
        Config::set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);

        $record = Item::query()->orderBy('id')->firstOrFail();

        $this->artisan('modularous:cache:warm-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--id' => (string) $record->getKey(),
            '--locale' => 'en',
            '--dry-run' => true,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Would queue WarmPresentationItemJob: TestModule::Item (1 records)')
            ->expectsOutputToContain('[queue, locale=en]');

        Bus::assertNotDispatched(WarmPresentationItemJob::class);
    }

    /** @test */
    public function dry_run_verbose_lists_each_record_line_in_sync_mode(): void
    {
        Bus::fake();

        $records = Item::query()->orderBy('id')->get();

        $command = $this->artisan('modularous:cache:warm-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--sync' => true,
            '--dry-run' => true,
            '--verbose' => true,
        ])->assertExitCode(0);

        foreach ($records as $record) {
            $command->expectsOutputToContain(
                "Would warmup presentationItem: TestModule::Item id={$record->getKey()}, locale=all [sync]",
            );
        }
    }
}
