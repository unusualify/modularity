<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Console\Blueprint\Concerns\ReportsBlueprintPlan;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;

/**
 * Create one ModuleRoute Blueprint provider class.
 *
 * @example php artisan modularous:make:blueprint:field Cms StyleSheet columns --dry-run
 * @example php artisan modularous:make:blueprint:field PressRelease PressRelease form_actions --from-config
 */
class MakeBlueprintFieldCommand extends BaseCommand
{
    use ReportsBlueprintPlan;

    protected $name = 'modularous:make:blueprint:field';

    protected $aliases = [
        'mod:c:blueprint:field',
        'modularous:create:blueprint:field',
    ];

    protected $description = 'Create a single ModuleRoute Blueprint provider ({Route}{Surface}{Field}).';

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
        $fieldArg = (string) $this->argument('field');
        $force = (bool) $this->option('force');
        $fromConfig = (bool) $this->option('from-config');
        $dryRun = (bool) $this->option('dry-run');

        try {
            $def = BlueprintFieldCatalog::get($fieldArg);
            $fieldKey = BlueprintFieldCatalog::normalize($fieldArg);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return E_ERROR;
        }

        /** @var string $fieldKey */
        $route = $module->route($routeStudly);
        $config = $route?->config() ?? $module->getRouteConfig($routeStudly);
        $items = $this->resolveBlueprintSeed(
            $module,
            $routeStudly,
            $fieldKey,
            is_array($config) ? $config : [],
            $fromConfig
        );

        $writer = new BlueprintClassWriter($this->filesystem);
        $row = $this->blueprintPlanRow(
            $writer,
            $module,
            $routeStudly,
            $fieldKey,
            $items,
            $force,
            $fromConfig
        );

        $this->printBlueprintPlan([$row], $dryRun);

        if ($dryRun) {
            if ($row['action'] === 'skip') {
                $this->warn('Nothing to write (already exist? use --force).');

                return E_ERROR;
            }

            $this->info('[dry-run] 1 file would be written.');

            return 0;
        }

        if ($row['action'] === 'skip') {
            $this->warn('Skipped existing ' . $routeStudly . $def['class_suffix'] . ' (use --force).');

            return E_ERROR;
        }

        $path = $writer->write($module, $routeStudly, $fieldKey, $items, $force);

        if ($path === null) {
            $this->warn('Skipped existing ' . $routeStudly . $def['class_suffix'] . ' (use --force).');

            return E_ERROR;
        }

        $this->info("Created: {$path}");
        $this->line('FQCN: ' . $row['fqcn']);

        return 0;
    }

    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::REQUIRED, 'The module name.'],
            ['route', InputArgument::REQUIRED, 'The route name (Studly or snake).'],
            ['field', InputArgument::REQUIRED, 'Blueprint field (inputs, columns, options, filters, form_actions, …).'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite an existing Blueprint class.'],
            ['from-config', null, InputOption::VALUE_NONE, 'Seed class body from legacy config array.'],
            ['dry-run', null, InputOption::VALUE_NONE, 'Show planned Blueprint write without creating the file.'],
        ];
    }
}
