<?php

namespace Unusualify\Modularity\Tests;

use Oobook\Database\Eloquent\ManageEloquentServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Unusualify\Modularity\Activators\ModularityActivator;


abstract class ModelTestCase extends TestCase
{
    public $path;

    public $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();

        // now de-register all the roles and permissions by clearing the permission cache
        // $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            ManageEloquentServiceProvider::class,
            ActivitylogServiceProvider::class
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('activitylog', [
            'enabled' => false,
            'delete_records_older_than_days' => 365,
            'default_log_name' => 'default',
            'default_auth_driver' => null,
            'subject_returns_soft_deleted_models' => false,
            'activity_model' => Activity::class,
            'table_name' => 'sp_activity_log',
            'database_connection' => 'testdb',
        ]);

        $app['config']->set('auth.guards.modularity', [
            'driver' => 'session',
            'provider' => 'modularity_users',
        ]);
        $app['config']->set('auth.providers.modularity_users', [
            'driver' => 'eloquent',
            'model' => \Unusualify\Modularity\Entities\User::class,
        ]);
        $app['config']->set('auth.passwords.modularity_users', [
            'provider' => 'modularity_users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ]);
    }
}

