<?php

namespace Unusualify\Modularity\Tests;

use Illuminate\Database\Schema\Blueprint;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
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
            \Oobook\Database\Eloquent\ManageEloquentServiceProvider::class,
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

        $app['config']->set('modularity.admin_app_url', 'http://admin.modularity.test');
        $app['config']->set('geoip.service', 'ipapi');
        $app['config']->set('geoip.services.ipapi', [
            'class' => \Torann\GeoIP\Services\IPApi::class,
            'secure' => true,
            'key' => env('IPAPI_KEY'),
            'continent_path' => storage_path('app/continents.json'),
            'lang' => 'en',
        ]);

        $app['config']->set('modularity.vite.dev_server', false);
        $app['config']->set('modularity.vite.build_path', '/build');
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();
        // $schema->create(modularityConfig('tables.users', 'admin_users'), function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('company_id')->nullable();
        //     $table->string('name');
        //     $table->string('surname', 30)->nullable();
        //     $table->string('job_title')->nullable();
        //     $table->boolean('published')->default(false);
        //     $table->string('email')->unique();
        //     $table->string('language')->default('en');
        //     $table->string('timezone')->default('Europe/London');
        //     $table->string('phone', 20)->nullable();
        //     $table->string('country', 100)->nullable();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->rememberToken();
        //     $table->timestamps();
        // });

    }

    public function moduleDirectory(string $moduleName): string
    {
        return realpath("{$this->modulesPath}/{$moduleName}");
    }
}
