<?php

namespace Unusualify\Modularous\Console\Make;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Entities\Enums\Permission as PermissionEnum;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Support\Decomposers\ValidatorParser;

class MakeRoutePermissionsCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:make:route:permissions
                                {--module= : The name of the module.}
                                {--route= : The name of the route.}
                                {--dry-run : List permissions without writing to the database}';

    protected $aliases = [
        'modularous:create:route:permissions',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permissions for module routes';

    protected $argumentName = 'permissions';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $hasModule = false;
        $hasRoute = false;
        $module = null;
        $route = null;
        $permissions = PermissionEnum::cases();
        $guardName = Modularous::getAuthGuardName();

        if( $this->option('module')) {
            $module = $this->option('module');
            $module = Modularous::find($module);

            if(! $module) {
                $this->error("Module {$module} not found!");
                return 1;
            }

            $hasModule = true;
        }

        if( $hasModule && $this->option('route')) {
            $route = $this->option('route');
            $hasRoute = true;

            if(! $module->hasRoute($route)) {
                $this->error("Route {$route} not found!");
                return 1;
            }
        }

        if($hasRoute) {
            $repository = $module->getRepository($route);

            if(! $repository) {
                $this->error("Repository for route {$route} not found!");
                return 1;
            }

            $this->createPermissions($repository, $permissions, $guardName, $module->getName(), $route, $dryRun);
        }

        if($hasModule) {
            foreach($module->getRoutes() as $route) {
                $repository = $module->getRepository($route);

                if(! $repository) {
                    $this->error("Repository for route {$route} not found!");
                    return 1;
                }

                $this->createPermissions($repository, $permissions, $guardName, $module->getName(), $route, $dryRun);
            }

        } else {
            $modules = Modularous::allEnabled();

            foreach($modules as $module) {
                foreach($module->getRoutes() as $route) {
                    $repository = $module->getRepository($route);

                    if(! $repository) {
                        $this->error("Repository for route {$route} not found!");
                        return 1;
                    }

                    $this->createPermissions($repository, $permissions, $guardName, $module->getName(), $route, $dryRun);
                }
            }
        }

        // $routeGenerator = new RouteGenerator($route);

        // $routeGenerator->createRoutePermissions();

        if($dryRun) {
            return 0;
        }

        $this->info('Permissions created successfully!');

        return 0;
    }


    private function createPermissions(Repository $repository, array $permissions, string $guardName, string $moduleName, string $routeName, bool $dryRun): void
    {
        $names = collect($permissions)
            ->map(fn ($permission) => $repository->getPermissionName($permission->value, $routeName))
            ->unique()
            ->values()
            ->all();

        $existing = Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        if ($dryRun) {
            collect($names)
                ->diff($existing)
                ->each(fn ($name) => $this->info("[dry-run] {$name}"));

            return;
        }

        $now = now();
        $rows = collect($names)
            ->diff($existing)
            ->map(fn (string $name) => [
                'name' => $name,
                'guard_name' => $guardName,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($rows !== []) {
            Permission::insert($rows);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
