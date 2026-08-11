<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\ModuleRoutePresentation\ModuleRoutePresentationResolver;

/**
 * Scaffold ModuleRoute Blueprint classes for a route.
 *
 * Default: FormInputs + IndexColumns.
 * --table-options / --options also creates IndexOptions.
 * --all creates every ADR field.
 *
 * @example php artisan modularous:make:blueprint Cms StyleSheet --from-config --write-config
 */
class MakeBlueprintCommand extends BaseCommand
{
    protected $name = 'modularous:make:blueprint';

    protected $aliases = [
        'mod:c:blueprint',
        'modularous:create:blueprint',
        'modularous:make:route-blueprint',
        'modularous:make:route-presentation',
        'modularous:create:route-presentation',
        'mod:c:route:presentation',
    ];

    protected $description = 'Create ModuleRoute Blueprint classes (FormInputs / IndexColumns / …) for a route.';

    public function __construct(protected Filesystem $filesystem)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var Module $module */
        $module = Modularous::findOrFail($this->argument('module'));

        if (! $module instanceof Module) {
            $this->error('Module is not a Modularous module.');

            return E_ERROR;
        }

        $routeStudly = studlyName((string) $this->argument('route'));
        $force = (bool) $this->option('force');
        $fromConfig = (bool) $this->option('from-config');
        $writeConfig = (bool) $this->option('write-config');
        $all = (bool) $this->option('all');
        $withOptions = (bool) $this->option('table-options') || (bool) $this->option('options');

        $fields = $all
            ? BlueprintFieldCatalog::allFieldKeys()
            : BlueprintFieldCatalog::defaultFieldKeys();

        if (! $all && $withOptions && ! in_array('options', $fields, true)) {
            $fields[] = 'options';
        }

        $only = $this->option('only');
        if (is_string($only) && $only !== '') {
            $fields = [];
            foreach (explode(',', $only) as $piece) {
                $key = BlueprintFieldCatalog::normalize(trim($piece));
                if ($key === null) {
                    $this->error("Unknown Blueprint field [{$piece}].");

                    return E_ERROR;
                }
                $fields[] = $key;
            }
            $fields = array_values(array_unique($fields));
        }

        $route = $module->route($routeStudly);
        $config = $route?->config() ?? $module->getRouteConfig($routeStudly);
        if (! is_array($config)) {
            $config = [];
        }

        $writer = new BlueprintClassWriter($this->filesystem);
        $written = [];
        $fqcns = [];

        foreach ($fields as $fieldKey) {
            $def = BlueprintFieldCatalog::get($fieldKey);
            $items = [];
            if ($fromConfig) {
                $legacy = $def['legacy_config_key'];
                $items = ModuleRoutePresentationResolver::readConfigPayloadFromArray(
                    is_array($config) ? $config : [],
                    $legacy
                );
            }

            $path = $writer->write($module, $routeStudly, $fieldKey, $items, $force);
            $fqcn = $writer->fqcn($module, $routeStudly, $fieldKey);
            $fqcns[$fieldKey] = $fqcn;

            if ($path === null) {
                $this->warn("Skipped existing {$routeStudly}{$def['class_suffix']}");

                continue;
            }

            $written[] = $path;
            $this->info("Created: {$path}");
        }

        if ($written === []) {
            $this->warn('No Blueprint files were written (already exist? use --force).');

            return E_ERROR;
        }

        if ($writeConfig) {
            $this->patchRouteConfig($module, $routeStudly, $fqcns, $fields);
        }

        $this->line('Convention FQCNs:');
        foreach ($fields as $fieldKey) {
            $this->line("  {$fieldKey}: " . ($fqcns[$fieldKey] ?? ''));
        }

        return 0;
    }

    /**
     * @param  array<string, string>  $fqcns
     * @param  list<string>  $fields
     */
    private function patchRouteConfig(Module $module, string $routeStudly, array $fqcns, array $fields): void
    {
        $configPath = $module->getConfigPath();
        if (! $this->filesystem->exists($configPath)) {
            $this->warn('Config file not found; skipped --write-config.');

            return;
        }

        $blueprint = [];
        foreach ($fields as $fieldKey) {
            $def = BlueprintFieldCatalog::get($fieldKey);
            $legacy = $def['legacy_config_key'];
            $blueprint[$legacy] = [
                'driver' => 'class',
                'class' => $fqcns[$fieldKey],
            ];
        }

        $snake = snakeCase($routeStudly);
        $key = $module->getSnakeName() . '.routes.' . $snake . '.blueprint';
        config([$key => $blueprint]);

        $this->comment(
            "Runtime config set for [{$key}]. Persist by adding 'blueprint' under routes.{$snake} in Config/config.php."
        );
        $this->line(var_export(['blueprint' => $blueprint], true));
    }

    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::REQUIRED, 'The module name.'],
            ['route', InputArgument::REQUIRED, 'The route name (Studly or snake).'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing Blueprint classes.'],
            ['from-config', null, InputOption::VALUE_NONE, 'Seed class bodies from existing config arrays.'],
            ['table-options', null, InputOption::VALUE_NONE, 'Also generate IndexOptions (alias of --options).'],
            ['options', null, InputOption::VALUE_NONE, 'Also generate IndexOptions.'],
            ['all', null, InputOption::VALUE_NONE, 'Generate all ADR Blueprint fields.'],
            ['only', null, InputOption::VALUE_REQUIRED, 'Comma-separated field list (inputs,columns,filters,…).'],
            ['write-config', null, InputOption::VALUE_NONE, 'Print/set blueprint driver wiring for config.'],
        ];
    }
}
