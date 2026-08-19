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
 * Scaffold ModuleRoute Blueprint classes for a route.
 *
 * Default: FormInputs + IndexColumns.
 * --table-options / --options also creates IndexOptions.
 * --all creates every ADR field.
 *
 * @example php artisan modularous:make:blueprint Cms StyleSheet --dry-run --from-config --write-config
 * @example php artisan modularous:make:blueprint Cms StyleSheet --from-config --write-config
 */
class MakeBlueprintCommand extends BaseCommand
{
    use ReportsBlueprintPlan;

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
        $dryRun = (bool) $this->option('dry-run');
        $commentFlats = ! (bool) $this->option('keep-flats');
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
        $plan = [];
        $seedByField = [];

        foreach ($fields as $fieldKey) {
            $items = $this->resolveBlueprintSeed(
                $module,
                $routeStudly,
                $fieldKey,
                $config,
                $fromConfig
            );
            $seedByField[$fieldKey] = $items;
            $plan[] = $this->blueprintPlanRow(
                $writer,
                $module,
                $routeStudly,
                $fieldKey,
                $items,
                $force,
                $fromConfig
            );
        }

        $this->printBlueprintPlan($plan, $dryRun);

        $configWritten = false;
        if ($writeConfig) {
            $configWritten = $this->persistRouteConfig($module, $routeStudly, $plan, $dryRun, $commentFlats);
        }

        if ($dryRun) {
            $writable = array_filter($plan, static fn (array $row): bool => $row['action'] !== 'skip');
            if ($writable === [] && ! $writeConfig) {
                $this->warn('Nothing to write (already exist? use --force).');

                return E_ERROR;
            }

            if ($writable !== []) {
                $this->info(sprintf('[dry-run] %d Blueprint file(s) would be written.', count($writable)));
            }

            return 0;
        }

        $written = [];
        foreach ($plan as $row) {
            if ($row['action'] === 'skip') {
                $this->warn("Skipped existing {$routeStudly}" . BlueprintFieldCatalog::get($row['field'])['class_suffix']);

                continue;
            }

            $path = $writer->write(
                $module,
                $routeStudly,
                $row['field'],
                $seedByField[$row['field']] ?? [],
                $force
            );

            if ($path === null) {
                continue;
            }

            $written[] = $path;
            $this->info("Created: {$path}");
        }

        if ($written === [] && ! $configWritten) {
            $this->warn('No Blueprint files were written (already exist? use --force).');

            return E_ERROR;
        }

        return 0;
    }

    /**
     * Persist nested index/form class leaves into Config/config.php (or preview with dry-run).
     *
     * @param  list<array{
     *     action: string,
     *     field: string,
     *     nested: string,
     *     legacy: string,
     *     path: string,
     *     fqcn: string,
     *     seed: string
     * }>  $plan
     */
    private function persistRouteConfig(
        Module $module,
        string $routeStudly,
        array $plan,
        bool $dryRun,
        bool $commentLegacyFlats,
    ): bool {
        $configPath = $module->getConfigPath();
        if (! $this->filesystem->exists($configPath)) {
            $this->warn('Config file not found; skipped --write-config.');

            return false;
        }

        $wires = [];
        foreach ($plan as $row) {
            $wires[] = [
                'nested' => $row['nested'],
                'fqcn' => $row['fqcn'],
                'legacy' => $row['legacy'],
            ];
        }

        $snake = snakeCase($routeStudly);
        $persister = new BlueprintConfigPersister;
        $original = (string) $this->filesystem->get($configPath);
        $result = $persister->build($original, $snake, $wires, $commentLegacyFlats);

        $this->printBlueprintWriteConfigPlan($plan, $snake, $dryRun);

        if (! $result['ok']) {
            $this->error($result['message']);

            return false;
        }

        if ($result['content'] === $original) {
            $this->comment("Config already up to date: {$configPath}");

            return true;
        }

        if ($dryRun) {
            $this->info("[dry-run] would update {$configPath}");
            if ($result['commented_flats'] !== []) {
                $this->line('[dry-run] would comment legacy flats: ' . implode(', ', $result['commented_flats']));
            }

            return true;
        }

        $this->filesystem->put($configPath, $result['content']);
        $this->info("Updated config: {$configPath}");
        if ($result['commented_flats'] !== []) {
            $this->line('Commented legacy flats: ' . implode(', ', $result['commented_flats']));
        }

        // Keep runtime in sync for the current process.
        $blueprint = [];
        foreach ($plan as $row) {
            $blueprint[$row['legacy']] = [
                'driver' => 'class',
                'class' => $row['fqcn'],
            ];
        }
        $key = $module->getSnakeName() . '.routes.' . $snake . '.blueprint';
        config([$key => $blueprint]);

        return true;
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
            ['write-config', null, InputOption::VALUE_NONE, 'Persist nested index/form class leaves into Config/config.php.'],
            ['keep-flats', null, InputOption::VALUE_NONE, 'With --write-config, do not comment out legacy flat keys.'],
            ['dry-run', null, InputOption::VALUE_NONE, 'Show planned Blueprint/config writes without creating files.'],
        ];
    }
}
