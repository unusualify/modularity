<?php

namespace Unusualify\Modularity\Tests;

use Nwidart\Modules\LaravelModulesServiceProvider;
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
