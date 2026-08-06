<?php

namespace Unusualify\Modularous\Console\Remake;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use ReflectionClass;
use Unusualify\Modularous\Console\Remake\Concerns\EnsuresPhpTraits;
use Unusualify\Modularous\Entities\Traits\HasRevisions;
use Unusualify\Modularous\Entities\Traits\IsSingular;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Traits\RevisionsTrait;

/**
 * Align an existing module route with revisions:
 * model {@see HasRevisions}, repository {@see RevisionsTrait},
 * plus revision entity / migration / optional one-time operation when not {@see IsSingular}.
 */
class RemakeRevisionsCommand extends Command
{
    use EnsuresPhpTraits;

    protected $signature = 'modularous:remake:revisions
        {module : Module name (e.g. Blog)}
        {route : Route / submodule key (e.g. Blog)}
        {--force : Overwrite revision entity / migration / operation when present}
        {--dry-run : Report changes without writing files}
        {--operation : Create the one-time revision table operation (off by default)}
        {--run-operation : Create the operation (implies --operation) then process tag [revision]}';

    protected $description = 'Remake revisions wiring: HasRevisions + RevisionsTrait (+ entity/migration; pass --operation for one-time op).';

    protected $aliases = [
        'm:remake:revisions',
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
        $createOperation = (bool) $this->option('operation') || (bool) $this->option('run-operation');

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
        $changed = $this->ensureTraitOnClass($modelClass, HasRevisions::class, $dryRun) || $changed;
        $changed = $this->ensureTraitOnClass($repositoryClass, RevisionsTrait::class, $dryRun) || $changed;

        $isSingular = classHasTrait($modelClass, IsSingular::class);
        if ($isSingular) {
            $this->comment('  · Singular model uses builtin SingletonRevision — skipping per-route revision entity/migration/operation.');
        } else {
            $studly = Str::studly($routeKey);
            $singular = Str::snake(Str::singular($studly));
            $revisionClass = $studly . 'Revision';
            $revisionFqcn = $module->getBaseNamespace() . '\\Entities\\Revisions\\' . $revisionClass;

            $modelInstance = new $modelClass;
            $parentTable = method_exists($modelInstance, 'getTable')
                ? (string) $modelInstance->getTable()
                : Str::plural($singular);

            $revisionsTable = "{$singular}_revisions";
            $foreignKey = "{$singular}_id";
            $relation = Str::camel($singular);

            Stub::setBasePath(dirname(__DIR__) . '/stubs');

            $changed = $this->ensureRevisionEntity(
                $module,
                $revisionClass,
                $revisionFqcn,
                $modelClass,
                $studly,
                $foreignKey,
                $relation,
                $revisionsTable,
                $dryRun,
            ) || $changed;

            $changed = $this->ensureRevisionModelProperty($modelClass, $revisionFqcn, $dryRun) || $changed;

            $changed = $this->ensureRevisionsMigration(
                $module,
                $singular,
                $parentTable,
                $revisionsTable,
                $dryRun,
            ) || $changed;

            if ($createOperation) {
                $changed = $this->ensureRevisionsOperation(
                    $module,
                    $routeKey,
                    $singular,
                    $parentTable,
                    $revisionsTable,
                    $dryRun,
                ) || $changed;
            }
        }

        if ($changed && ! $dryRun && $this->option('run-operation') && $createOperation && ! $isSingular) {
            $this->info('Processing operations with tag [revision]…');
            // ProcessOperationsCommand hardcodes tag modularous; call the package command with the revision tag.
            Artisan::call('operations:process', [
                '--tag' => ['revision'],
                '--sync' => true,
            ], $this->output);
        }

        if (! $changed) {
            $this->info("Revisions already aligned for {$module->getStudlyName()}::{$routeKey}.");
        } elseif ($dryRun) {
            $this->comment('Dry-run complete (no files written).');
        } else {
            $this->info("Remade revisions for {$module->getStudlyName()}::{$routeKey}.");
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

    private function ensureRevisionEntity(
        Module $module,
        string $revisionClass,
        string $revisionFqcn,
        string $parentModelFqcn,
        string $parentStudly,
        string $foreignKey,
        string $relation,
        string $revisionsTable,
        bool $dryRun,
    ): bool {
        $dir = $module->getDirectoryPath('Entities/Revisions');
        $path = concatenate_path($dir, $revisionClass . '.php');

        if (is_file($path) && ! $this->option('force')) {
            $this->line("  · Revision entity already exists → {$path}");

            return false;
        }

        $namespace = $module->getBaseNamespace() . '\\Entities\\Revisions';
        $contents = (new Stub('/models/revision_model.stub', [
            'NAMESPACE' => $namespace,
            'CLASS' => $revisionClass,
            'PARENT_MODEL' => $parentModelFqcn,
            'PARENT_CLASS' => $parentStudly,
            'FOREIGN_KEY' => $foreignKey,
            'RELATION' => $relation,
            'TABLE' => $revisionsTable,
        ]))->render();

        $action = is_file($path) ? 'Overwrite revision entity' : 'Create revision entity';
        $this->info(($dryRun ? '[dry-run] ' : '') . "{$action} → {$path}");

        if ($dryRun) {
            return true;
        }

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        File::put($path, $contents);

        return true;
    }

    /**
     * @param  class-string  $modelClass
     * @param  class-string  $revisionFqcn
     */
    private function ensureRevisionModelProperty(string $modelClass, string $revisionFqcn, bool $dryRun): bool
    {
        if (property_exists($modelClass, 'revisionModel')) {
            $this->line("  · {$modelClass} already declares \$revisionModel");

            return false;
        }

        $path = (new ReflectionClass($modelClass))->getFileName();
        if ($path === false || ! is_readable($path)) {
            $this->warn("  · Cannot read source for {$modelClass}");

            return false;
        }

        $contents = File::get($path);
        if (preg_match('/\$revisionModel\b/', $contents)) {
            $this->line("  · {$modelClass} already declares \$revisionModel");

            return false;
        }

        $short = class_basename($revisionFqcn);
        $contents = $this->ensurePhpImport($contents, $revisionFqcn);

        if (! preg_match('/class\s+\w+[^{]*\{/', $contents, $classMatch, PREG_OFFSET_CAPTURE)) {
            $this->warn("  · Could not add \$revisionModel to {$modelClass}");

            return false;
        }

        $classBodyStart = $classMatch[0][1] + strlen($classMatch[0][0]);
        $slice = substr($contents, $classBodyStart);
        $property = "\n    protected string \$revisionModel = {$short}::class;\n";

        if (preg_match('/^(\s*)use\s+[^;]+;/m', $slice, $inner, PREG_OFFSET_CAPTURE)) {
            $afterUse = $classBodyStart + $inner[0][1] + strlen($inner[0][0]);
            $contents = substr($contents, 0, $afterUse) . $property . substr($contents, $afterUse);
        } else {
            $contents = substr($contents, 0, $classBodyStart) . $property . substr($contents, $classBodyStart);
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Add \$revisionModel → {$modelClass}");

        if (! $dryRun) {
            File::put($path, $contents);
        }

        return true;
    }

    private function ensureRevisionsMigration(
        Module $module,
        string $singular,
        string $parentTable,
        string $revisionsTable,
        bool $dryRun,
    ): bool {
        $migrationFolder = GenerateConfigReader::read('migration')->getPath();
        $migrationsPath = $module->getDirectoryPath($migrationFolder);
        $pattern = concatenate_path($migrationsPath, "*_create_{$singular}_revisions_table.php");
        $existing = glob($pattern) ?: [];

        if ($existing !== [] && ! $this->option('force')) {
            $this->line('  · Revisions migration already exists → ' . $existing[0]);

            return false;
        }

        $fileName = Carbon::now()->format('Y_m_d_His') . "_create_{$singular}_revisions_table.php";
        $path = concatenate_path($migrationsPath, $fileName);

        if ($existing !== [] && $this->option('force')) {
            $path = $existing[0];
        }

        $contents = (new Stub('/remake/revisions-migration.stub', [
            'REVISIONS_TABLE' => $revisionsTable,
            'SINGULAR' => $singular,
            'PARENT_TABLE' => $parentTable,
        ]))->render();

        $action = is_file($path) ? 'Overwrite revisions migration' : 'Create revisions migration';
        $this->info(($dryRun ? '[dry-run] ' : '') . "{$action} → {$path}");

        if ($dryRun) {
            return true;
        }

        if (! is_dir($migrationsPath)) {
            mkdir($migrationsPath, 0777, true);
        }

        File::put($path, $contents);

        return true;
    }

    private function ensureRevisionsOperation(
        Module $module,
        string $routeKey,
        string $singular,
        string $parentTable,
        string $revisionsTable,
        bool $dryRun,
    ): bool {
        $operationsDir = base_path(config('one-time-operations.directory', 'operations'));
        $snakeName = Str::snake($module->getStudlyName())
            . '_' . Str::snake(Str::studly($routeKey))
            . '_revisions_table';
        $pattern = concatenate_path($operationsDir, "*_{$snakeName}_operation.php");
        $existing = glob($pattern) ?: [];

        if ($existing === []) {
            // Also match shorter descriptive names that already guard this table.
            $loose = glob(concatenate_path($operationsDir, "*{$singular}_revisions*_operation.php")) ?: [];
            $existing = $loose;
        }

        if ($existing !== [] && ! $this->option('force')) {
            $this->line('  · Revisions operation already exists → ' . $existing[0]);

            return false;
        }

        $fileName = Carbon::now()->format('Y_m_d_His') . "_{$snakeName}_operation.php";
        $path = concatenate_path($operationsDir, $fileName);

        if ($existing !== [] && $this->option('force')) {
            $path = $existing[0];
        }

        $headline = Str::headline($module->getStudlyName() . ' ' . Str::studly($routeKey) . ' revisions table');

        $contents = (new Stub('/remake/revisions-operation.stub', [
            'HEADLINE' => $headline,
            'REVISIONS_TABLE' => $revisionsTable,
            'PARENT_TABLE' => $parentTable,
            'SINGULAR' => $singular,
        ]))->render();

        $action = is_file($path) ? 'Overwrite revisions operation' : 'Create revisions operation';
        $this->info(($dryRun ? '[dry-run] ' : '') . "{$action} → {$path}");

        if ($dryRun) {
            return true;
        }

        if (! is_dir($operationsDir)) {
            mkdir($operationsDir, 0777, true);
        }

        File::put($path, $contents);

        return true;
    }
}
