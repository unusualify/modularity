<?php

namespace Unusualify\Modularous\Console\Sync;

use ReflectionMethod;
use Spatie\Permission\Models\Permission;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Entities\Enums\Permission as PermissionEnum;
use Unusualify\Modularous\Entities\Traits\HasRevisions;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousFinder;

class SyncCachePermissionsCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'modularous:sync:cache-permissions
        {--dry-run : List permissions without writing to the database}';

    /**
     * @var string
     */
    protected $description = 'Create Spatie permissions for cache routes ({route}_cache)';

    public function handle(): int
    {
        $models = ModularousFinder::getAllModuleRouteModels();
        $repositories = ModularousFinder::getAllModuleRouteRepositories();
        // $guard = config('auth.defaults.guard', 'web');
        $guard = Modularous::getAuthGuardName();

        $suffixes = [
            PermissionEnum::CACHING->value,
        ];

        $created = [];

        foreach ($repositories as $repositoryClass) {
            foreach ($suffixes as $suffix) {
                $permissionName = $repositoryClass->getPermissionName($suffix);

                if ($this->option('dry-run')) {
                    $this->line("[dry-run] {$permissionName}");

                    continue;
                }

                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => $guard],
                    []
                );

                if ($permission->wasRecentlyCreated) {
                    $created[] = $permissionName;
                }
            }
        }

        if ($this->option('dry-run')) {
            return 0;
        }

        foreach ($created as $name) {
            $this->info("Created permission: {$name}");
        }

        $this->info('Revision permissions synced.');

        return 0;
    }

    /**
     * Uses {@see HasRevisions::revisionPermissionPrefix()} when overridden on the model.
     *
     * @return list<string>
     */
    protected function resolveRoutePrefixesForModel(string $modelClass): array
    {
        $method = new ReflectionMethod($modelClass, 'revisionPermissionPrefix');

        if ($method->getDeclaringClass()->getName() === HasRevisions::class) {
            $this->warn("Skipping {$modelClass}: override protected function revisionPermissionPrefix(): ?string (kebab-case route name).");

            return [];
        }

        $method->setAccessible(true);
        $instance = new $modelClass;
        $prefix = $method->invoke($instance);

        if (! is_string($prefix) || $prefix === '') {
            $this->warn("Skipping {$modelClass}: revisionPermissionPrefix() returned empty.");

            return [];
        }

        return [$prefix];
    }
}
