<?php

namespace Unusualify\Modularous\Console\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Scaffolds an invokable CMS public controller extending {@see CmsController}.
 *
 * Intended for use when adding CMS submodules / routes and for future {@code HasCms}-style integration.
 */
class MakeCmsControllerCommand extends Command
{
    protected $signature = 'modularous:make:cms-controller
        {module : Module name (e.g. Cms)}
        {route : Route/submodule key (e.g. page)}
        {--force : Overwrite if the file already exists}';

    protected $description = 'Create a CMS public front controller (CmsController subclass).';

    protected $aliases = [
        'm:m:cms-controller',
    ];

    public function handle(): int
    {
        if (! modularousConfig('cms_features.enabled')) {
            $this->error('CMS features are not enabled.');

            return self::FAILURE;
        }

        $module = Modularous::findOrFail($this->argument('module'));

        $routeKey = trim((string) $this->argument('route'));
        if ($routeKey === '') {
            $this->error('Route key cannot be empty.');

            return self::FAILURE;
        }

        $routeKey = Str::slug($routeKey, '_');
        $studlyRoute = Str::studly(str_replace('_', ' ', $routeKey));
        $className = $studlyRoute . 'CmsController';

        $namespace = config('modules.namespace', 'Modules') . '\\' . $module->getStudlyName() . '\\Http\\Controllers\\Front';
        $path = $module->getPath() . '/Http/Controllers/Front/' . $className . '.php';

        Stub::setBasePath(dirname(__DIR__) . '/stubs');

        $contents = (new Stub('/cms-controller.stub', [
            'NAMESPACE' => $namespace,
            'CLASS' => $className,
            'MODULE_STUDLY' => $module->getStudlyName(),
            'ROUTE_STUDLY' => $studlyRoute,
        ]))->render();

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        try {
            (new FileGenerator($path, $contents))->withFileOverwrite((bool) $this->option('force'))->generate();
        } catch (FileAlreadyExistException $e) {
            $this->error("File already exists: {$path}");

            return self::FAILURE;
        }

        $this->info("Created [{$className}] at {$path}");

        return self::SUCCESS;
    }
}
