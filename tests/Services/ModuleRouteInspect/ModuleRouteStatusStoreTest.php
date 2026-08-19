<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ModuleRouteInspect;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\ModuleRouteStatus;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspector;
use Unusualify\Modularous\Services\ModuleRouteInspect\Stores\DatabaseModuleRouteStatusStore;
use Unusualify\Modularous\Services\ModuleRouteInspect\Stores\FilesystemModuleRouteStatusStore;
use Unusualify\Modularous\Tests\TestCase;

class ModuleRouteStatusStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $table = modularousConfig('tables.module_route_statuses', 'um_module_route_statuses');

        if (! Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $blueprint): void {
                $blueprint->increments('id');
                $blueprint->string('module');
                $blueprint->string('route');
                $blueprint->boolean('enabled')->default(true);
                $blueprint->timestamps();
                $blueprint->unique(['module', 'route']);
            });
        }
    }

    /** @test */
    public function it_binds_filesystem_store_by_default(): void
    {
        $this->assertInstanceOf(
            FilesystemModuleRouteStatusStore::class,
            $this->app->make(ModuleRouteStatusStoreInterface::class)
        );
        $this->assertTrue($this->app->bound(ModuleRouteInspector::class));
    }

    /** @test */
    public function it_can_resolve_database_driver_from_config(): void
    {
        $this->bindDriver('database');

        $this->assertInstanceOf(
            DatabaseModuleRouteStatusStore::class,
            $this->app->make(ModuleRouteStatusStoreInterface::class)
        );
    }

    /** @test */
    public function database_store_persists_and_reads_statuses(): void
    {
        $store = new DatabaseModuleRouteStatusStore;

        $store->setEnabled('Blog', 'posts', true);
        $store->setEnabled('Blog', 'categories', false);

        $this->assertTrue($store->isEnabled('Blog', 'posts'));
        $this->assertFalse($store->isEnabled('Blog', 'categories'));
        $this->assertFalse($store->isEnabled('Blog', 'missing'));

        $this->assertEquals(
            [
                'Posts' => true,
                'Categories' => false,
            ],
            $store->getStatuses('Blog')
        );

        $this->assertDatabaseHas(ModuleRouteStatus::make()->getTable(), [
            'module' => 'Blog',
            'route' => 'Posts',
            'enabled' => 1,
        ]);
    }

    /** @test */
    public function database_store_ensure_exists_is_a_noop(): void
    {
        $store = new DatabaseModuleRouteStatusStore;
        $store->ensureExists('Blog');

        $this->assertSame([], $store->getStatuses('Blog'));
    }

    private function bindDriver(string $driver): void
    {
        config(['modularous.module_route_inspect.driver' => $driver]);

        $this->app->forgetInstance(ModuleRouteStatusStoreInterface::class);
        $this->app->singleton(ModuleRouteStatusStoreInterface::class, function ($app) {
            $configured = (string) config('modularous.module_route_inspect.driver', 'filesystem');

            return match ($configured) {
                'database' => $app->make(DatabaseModuleRouteStatusStore::class),
                default => $app->make(FilesystemModuleRouteStatusStore::class),
            };
        });
    }
}
