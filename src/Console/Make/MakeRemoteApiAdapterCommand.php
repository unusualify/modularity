<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Make;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;

class MakeRemoteApiAdapterCommand extends BaseCommand
{
    protected $signature = 'modularous:make:remote-api-adapter
        {module : The module name}
        {route : The route/submodule name}
        {--dto : Generate DTO only}
        {--adapter : Generate adapter only}
        {--connector : Generate connector only}
        {--all : Generate DTO, adapter and connector}
        {--endpoint= : Initial API endpoint}
        {--f|force : Overwrite existing files}';

    protected $description = 'Generate Remote API adapter classes for a module route.';

    protected $aliases = [
        'modularous:make:remote-api',
        'mod:c:remote-api-adapter',
    ];

    public function handle(): int
    {
        Stub::setBasePath(dirname(__DIR__) . '/stubs');

        $moduleName = $this->argument('module');
        $routeName = snakeCase($this->argument('route'));
        $module = Modularous::findOrFail($moduleName);
        $studlyRoute = studlyName($routeName);
        $endpoint = (string) ($this->option('endpoint') ?: Str::kebab($studlyRoute));

        $generateAll = (bool) $this->option('all')
            || (! $this->option('dto') && ! $this->option('adapter') && ! $this->option('connector'));

        $targets = [
            'dto' => (bool) ($generateAll || $this->option('dto')),
            'adapter' => (bool) ($generateAll || $this->option('adapter')),
            'connector' => (bool) ($generateAll || $this->option('connector')),
        ];

        $directory = $module->getPath() . '/RemoteApi';
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $replacements = [
            'MODULE' => $module->getStudlyName(),
            'ROUTE' => $studlyRoute,
            'ENDPOINT' => $endpoint,
        ];

        $namespace = config('modules.namespace', 'Modules') . '\\' . $module->getStudlyName() . '\\RemoteApi';
        $classMap = [
            'dto' => [$studlyRoute . 'RemoteApiDto', 'remote-api/dto.stub'],
            'adapter' => [$studlyRoute . 'RemoteApiAdapter', 'remote-api/adapter.stub'],
            'connector' => [$studlyRoute . 'RemoteApiConnector', 'remote-api/connector.stub'],
        ];

        foreach ($classMap as $type => [$className, $stubPath]) {
            if (! $targets[$type]) {
                continue;
            }

            $path = $directory . '/' . $className . '.php';

            if (File::exists($path) && ! $this->option('force')) {
                $this->warn("Skipped {$className} — already exists (use --force to overwrite).");

                continue;
            }

            File::put($path, (new Stub('/' . $stubPath, $replacements))->render());
            $this->info("Created {$namespace}\\{$className}");
        }

        return 0;
    }
}
