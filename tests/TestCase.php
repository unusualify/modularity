<?php

namespace Unusualify\Modularity\Tests;

use Nwidart\Modules\LaravelModulesServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Unusualify\Modularity\Activators\ModularityActivator;
use Unusualify\Modularity\LaravelServiceProvider;
use Unusualify\Modularity\Providers\ModularityProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public $path;

    public $modulesPath;


    protected function setUp(): void
    {
        parent::setUp();

        // Note: this also flushes the cache from within the migration
        $this->setUpDatabase($this->app);

        $this->path = realpath(__DIR__ . '/..');

        $this->modulesPath = realpath($this->path . '/modules');

        // $app['cache'] = $this->createMock(CacheManager::class);
        // $app['files'] = $this->createMock(Filesystem::class);
        // $app['config'] = $this->createMock(Config::class);

    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModulesServiceProvider::class,
            LaravelServiceProvider::class,
            ModularityProvider::class,
            PermissionServiceProvider::class,
            \Oobook\Priceable\LaravelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testdb');
        $app['config']->set('database.connections.testdb', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.prefix', 'spatie_tests---');
        $app['config']->set('cache.default', getenv('CACHE_DRIVER') ?: 'array');
        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.cache.enabled', false);
        $app['config']->set('modules.scan.paths', [
            base_path('vendor/*/*'),
            realpath(__DIR__ . '/../modules'),
        ]);

        $app['config']->set('modules.activators.modularity', [
            'class' => ModularityActivator::class,
            'statuses-file' => base_path('modules_statuses.json'),
            'cache-key' => 'modularity.activator.installed',
            'cache-lifetime' => 604800,
        ]);

        $app['config']->set('modules.activator', 'modularity');

    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        // $schema->create('users', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
        //     $table->string('email');
        //     $table->softDeletes();
        // });

        // $schema->create('companies', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // $schema->create('files', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->uuidMorphs('fileable');
        //     $table->string('name');
        // });

    }

    public function moduleDirectory(string $moduleName): string
    {
        return realpath("{$this->modulesPath}/{$moduleName}");
    }
}
