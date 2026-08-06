<?php

namespace Unusualify\Modularous\Console\Remake;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Concerns\IsCmr;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Modules\Cms\Repositories\Traits\CmrTrait;
use Modules\Cms\Support\CmsFrontRouteRegistrationCache;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularous\Console\Remake\Concerns\EnsuresPhpTraits;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;

/**
 * Align an existing module route with the CMR contract:
 * model {@see IsCmr}, repository {@see CmrTrait}, Front controller extending {@see CmsController}.
 */
class RemakeCmrCommand extends Command
{
    use EnsuresPhpTraits;

    protected $signature = 'modularous:remake:cmr
        {module : Module name (e.g. PrimaryPage)}
        {route : Route / submodule key (e.g. AboutUs)}
        {--force : Overwrite Front controller when converting from BaseController}
        {--dry-run : Report changes without writing files}';

    protected $description = 'Remake CMR wiring: IsCmr + CmrTrait + Front CmsController for a module route.';

    protected $aliases = [
        'm:remake:cmr',
    ];

    public function handle(): int
    {
        $module = Modularous::findOrFail($this->argument('module'));
        if (! $module instanceof Module) {
            $this->error('Module not found.');

            return self::FAILURE;
        }

        $routeKey = $this->resolveEnabledRoute($module, (string) $this->argument('route'));
        if ($routeKey === null) {
            $this->error('Route not found or not enabled on module [' . $module->getStudlyName() . '].');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        try {
            $modelClass = $module->getModel($routeKey, false);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $repositoryClass = $module->getRepository($routeKey, false);
        if (! is_string($repositoryClass) || $repositoryClass === '' || ! class_exists($repositoryClass)) {
            $this->error("Repository not found for [{$routeKey}].");

            return self::FAILURE;
        }

        if (! is_string($modelClass) || $modelClass === '' || ! class_exists($modelClass)) {
            $this->error("Model not found for [{$routeKey}].");

            return self::FAILURE;
        }

        $changed = false;
        $changed = $this->ensureTraitOnClass($modelClass, IsCmr::class, $dryRun) || $changed;
        $changed = $this->ensureTraitOnClass($repositoryClass, CmrTrait::class, $dryRun) || $changed;
        $changed = $this->ensureCmsFrontController($module, $routeKey, $dryRun) || $changed;

        if ($changed && ! $dryRun && class_exists(CmsFrontRouteRegistrationCache::class)) {
            CmsFrontRouteRegistrationCache::clearPersistent();
            $this->line('Cleared front-route registration cache.');
        }

        if (! $changed) {
            $this->info("CMR already aligned for {$module->getStudlyName()}::{$routeKey}.");
        } elseif ($dryRun) {
            $this->comment('Dry-run complete (no files written).');
        } else {
            $this->info("Remade CMR for {$module->getStudlyName()}::{$routeKey}.");
        }

        return self::SUCCESS;
    }

    private function resolveEnabledRoute(Module $module, string $routeSegment): ?string
    {
        foreach ($module->getRouteNames() as $routeName) {
            if (studlyName($routeName) === studlyName($routeSegment) && $module->isEnabledRoute($routeName)) {
                return $routeName;
            }
        }

        return null;
    }

    private function ensureCmsFrontController(Module $module, string $routeKey, bool $dryRun): bool
    {
        $studlyRoute = Str::studly($routeKey);
        $className = $studlyRoute . 'Controller';
        $fqcn = $module->getTargetClassNamespace('front-controller', $className);
        $path = $module->getTargetClassPath('front-controller', $className . '.php');

        if (class_exists($fqcn) && is_subclass_of($fqcn, CmsController::class, true)) {
            $this->line("  · Front {$fqcn} already extends CmsController");

            return false;
        }

        Stub::setBasePath(dirname(__DIR__) . '/stubs');
        $namespace = $module->getTargetClassNamespace('front-controller');
        $contents = (new Stub('/route-controller-front-cms.stub', [
            'NAMESPACE' => $namespace,
            'CLASS' => $className,
            'STUDLY_MODULE_NAME' => $module->getStudlyName(),
            'ROUTE_NAME' => $studlyRoute,
        ]))->render();

        $exists = is_file($path);
        if ($exists && ! $this->option('force') && ! $dryRun) {
            if (! $this->confirm("Overwrite Front controller [{$fqcn}] with CmsController stub?", true)) {
                $this->warn('  · Skipped Front controller conversion.');

                return false;
            }
        }

        $action = $exists ? 'Convert Front → CmsController' : 'Create Front CmsController';
        $this->info(($dryRun ? '[dry-run] ' : '') . "{$action} → {$path}");

        if ($dryRun) {
            return true;
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        File::put($path, $contents);

        return true;
    }
}
