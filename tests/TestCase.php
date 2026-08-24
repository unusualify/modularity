<?php

namespace Unusualify\Modularous\Tests;

use Astrotomic\Translatable\TranslatableServiceProvider;
use Illuminate\Foundation\Application;
use JoeDixon\Translation\TranslationServiceProvider;
use Modules\SystemPayment\Entities\Payment;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Nwidart\Modules\ModuleManifest;
use Oobook\Database\Eloquent\ManageEloquentServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionServiceProvider;
use Unusualify\Modularous\Activators\ModularousActivator;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\Entities\Observers\PriceableObserver;
use Unusualify\Modularous\LaravelServiceProvider;
use Unusualify\Modularous\Providers\ModularousProvider;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public $path;

    public $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Note: this also flushes the cache from within the migration
        $this->setUpDatabase($this->app);

        if (function_exists('forget_database_exists_cache')) {
            forget_database_exists_cache();
        }

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
            ModularousProvider::class,
            PermissionServiceProvider::class,
            ManageEloquentServiceProvider::class,
            \Oobook\Priceable\LaravelServiceProvider::class,
            TranslationServiceProvider::class,
            TranslatableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('database.default', 'testdb');
        $app['config']->set('database.connections.testdb', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // ShouldBroadcast events hardcode $connection = 'redis' and may broadcastVia('reverb').
        // Keep package tests free of phpredis / Pusher credentials.
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('queue.connections.redis', [
            'driver' => 'sync',
        ]);
        $app['config']->set('broadcasting.default', 'null');
        $app['config']->set('broadcasting.connections.reverb', [
            'driver' => 'null',
        ]);
        $app['config']->set('broadcasting.connections.pusher', [
            'driver' => 'null',
        ]);

        $app['config']->set('cache.prefix', 'spatie_tests---');
        $app['config']->set('cache.default', getenv('CACHE_DRIVER') ?: 'array');
        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.cache.enabled', false);
        $app['config']->set('modules.scan.paths', [
            base_path('vendor/*/*'),
            realpath(__DIR__ . '/../modules'),
        ]);

        $app['config']->set('modules.paths.modules', realpath(__DIR__ . '/../modules') ?: __DIR__ . '/../modules');

        $generatorPaths = [
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => false],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'model' => ['path' => 'Entities', 'generate' => true],
            'repository' => ['path' => 'Repositories', 'generate' => true],
            'routes' => ['path' => 'Routes', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'request' => ['path' => 'Http/Requests', 'generate' => true],
            'resource' => ['path' => 'Transformers', 'generate' => true],
            'lang' => ['path' => 'Resources/lang', 'generate' => true],
            'filter' => ['path' => 'Http/Middleware', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],
        ];

        $modularousGeneratorPaths = array_merge(config('modules.paths.generator'), $generatorPaths, [
            'route-controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'route-request' => ['path' => 'Http/Requests', 'generate' => true],
            'route-resource' => ['path' => 'Transformers', 'generate' => true],
        ]);

        $app['config']->set('modules.paths.generator', $modularousGeneratorPaths);
        $app['config']->set('modularous.paths.generator', $modularousGeneratorPaths);
        $app['config']->set('modularous.base_key', 'modularous');
        $app['config']->set('modularous.stubs.path', realpath(__DIR__ . '/../src/Console/stubs'));

        $statusesFile = 'modules_statuses_' . IsolatedTestModules::testTokenSuffix() . '.json';
        $statusFilePath = base_path($statusesFile);

        $app['files']->put($statusFilePath, json_encode([
            'SystemNotification' => false,
            'SystemPayment' => false,
            'SystemPricing' => false,
            'SystemSetting' => false,
            'SystemUser' => false,
            'SystemUtility' => false,
        ]));
        $app['config']->set('modules.activators.modularous', [
            'class' => ModularousActivator::class,
            'statuses-file' => $statusFilePath,
            'cache-key' => 'modularous.activator.installed.' . IsolatedTestModules::testTokenSuffix(),
            'cache-lifetime' => 604800,
        ]);

        $app['config']->set('modules.activator', 'modularous');
        $this->rebindModularousActivator($app);

        $app['config']->set('modularous.app_url', 'http://localhost');
        $app['config']->set('modularous.admin_app_url', '');
        $app['config']->set('modularous.admin_app_path', 'admin');
        $app['config']->set('modularous.admin_route_name_prefix', 'admin');

        $app['config']->set('modularous.media_library.image_service', "Unusualify\Modularous\Services\MediaLibrary\Local");
        $app['config']->set('modularous.file_library.file_service', "Unusualify\Modularous\Services\FileLibrary\Disk");

        $app['config']->set([
            'priceable.observers.price' => PriceableObserver::class,
            'priceable.prices_are_including_vat' => false,
        ]);
        $app['config']->set([
            'translatable.locales' => [
                'en',
            ],
        ]);

        $app['config']->set([
            'payable.table' => 'up_payments',
            'payable.model' => Payment::class,
            'payable.status_enum' => PaymentStatus::class,
            'payable.additional_fillable' => ['payment_service_id', 'price_id', 'currency_id'],
            // 'payable.middleware' => ['web.auth', 'modularous.panel'],
        ]);

        // ModelHelpers boots activity logging on every Model; CauserResolver requires this config.
        $app['config']->set('activitylog', [
            'enabled' => false,
            'delete_records_older_than_days' => 365,
            'default_log_name' => 'default',
            'default_auth_driver' => null,
            'subject_returns_soft_deleted_models' => false,
            'activity_model' => Activity::class,
            'table_name' => 'sp_activity_logs',
            'database_connection' => 'testdb',
        ]);
    }

    /**
     * Set up the database.
     *
     * @param Application $app
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

    /**
     * nwidart resolves ActivatorInterface during provider register (via ModuleManifest),
     * before getEnvironmentSetUp can switch modules.activator to modularous. Forget the
     * early FileActivator singleton so later resolves use the test activator config.
     */
    protected function rebindModularousActivator($app): void
    {
        $app->forgetInstance(ActivatorInterface::class);
        $app->forgetInstance(ModuleManifest::class);

        $modulesProperty = new \ReflectionProperty(FileRepository::class, 'modules');
        $modulesProperty->setValue(null, null);
    }
}
