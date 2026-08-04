<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Migration;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Entities\Traits\Publishable;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Support\PublishableMetadata;

/**
 * Scaffold a one-time operation (and optional code edits) that moves publishable
 * columns from a main table onto its translations table (locale-scoped publish).
 *
 * Example: php artisan modularous:publishable:make-translated Blog Blog --dates
 */
class PublishableMakeTranslatedCommand extends Command
{
    protected $signature = 'modularous:publishable:make-translated
                            {module : Module name (e.g. Blog)}
                            {route? : Route / entity name (defaults to module studly name)}
                            {--dates : Include publish_start_date / publish_end_date}
                            {--dry-run : Print the plan without writing files}
                            {--write-code : Also patch Inputs FILLABLE/TRANSLATED_ATTRIBUTES and Translation casts}
                            {--tag=production : One-time operation tag}';

    protected $aliases = [
        'modularous:make:translated-publishable',
    ];

    protected $description = 'Generate an operation to move Publishable columns onto translations for a module route';

    public function handle(): int
    {
        $moduleName = (string) $this->argument('module');
        $routeName = (string) ($this->argument('route') ?: $moduleName);
        $withDates = (bool) $this->option('dates');
        $dryRun = (bool) $this->option('dry-run');
        $writeCode = (bool) $this->option('write-code');

        $module = Modularous::find($moduleName);
        if ($module === null) {
            $this->error("Module [{$moduleName}] not found.");

            return self::FAILURE;
        }

        try {
            /** @var Model $model */
            $model = $module->getModel($routeName, true);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $modelClass = $model::class;

        if (! classHasTrait($model, Publishable::class)) {
            $this->error("{$modelClass} does not use Publishable.");

            return self::FAILURE;
        }

        if (! classHasTrait($model, HasTranslation::class)) {
            $this->error("{$modelClass} does not use HasTranslation.");

            return self::FAILURE;
        }

        $fields = $withDates
            ? PublishableMetadata::PUBLISHABLE_ATTRIBUTES
            : ['published'];

        $alreadyTranslated = array_values(array_filter(
            $fields,
            static fn (string $field): bool => method_exists($model, 'isTranslationAttribute')
                && $model->isTranslationAttribute($field),
        ));

        if ($alreadyTranslated === $fields) {
            $this->warn('Publishable fields are already listed on translatedAttributes — nothing to do.');

            return self::SUCCESS;
        }

        $mainTable = $model->getTable();
        $translationModelClass = property_exists($model, 'translationModel')
            ? (string) $model->translationModel
            : '';
        if ($translationModelClass === '' || ! class_exists($translationModelClass)) {
            $this->error('Unable to resolve translation model class from entity.');

            return self::FAILURE;
        }

        /** @var Model $translationModel */
        $translationModel = new $translationModelClass;
        $translationsTable = $translationModel->getTable();
        $foreignKey = property_exists($model, 'translationForeignKey')
            ? (string) $model->translationForeignKey
            : Str::snake(class_basename($modelClass)) . '_id';

        $this->table(['Key', 'Value'], [
            ['Module', $module->getStudlyName()],
            ['Route', $routeName],
            ['Model', $modelClass],
            ['Main table', $mainTable],
            ['Translations table', $translationsTable],
            ['Foreign key', $foreignKey],
            ['Fields', implode(', ', $fields)],
        ]);

        $operationPath = $this->operationPath($module->getStudlyName(), $routeName);

        if ($dryRun) {
            $this->info('[dry-run] Would write operation: ' . $operationPath);
            $this->line($this->buildOperationContents(
                $modelClass,
                $mainTable,
                $translationsTable,
                $foreignKey,
                $fields,
                $module->getStudlyName(),
                $routeName,
            ));

            if ($writeCode) {
                $this->info('[dry-run] Would also patch Inputs / Translation casts.');
            }

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($operationPath));
        File::put($operationPath, $this->buildOperationContents(
            $modelClass,
            $mainTable,
            $translationsTable,
            $foreignKey,
            $fields,
            $module->getStudlyName(),
            $routeName,
        ));
        $this->info('Operation written: ' . $operationPath);

        if ($writeCode) {
            $this->patchCodeFiles($module->getPath(), $routeName, $fields);
        } else {
            $this->newLine();
            $this->comment('Code checklist (or re-run with --write-code):');
            $this->line('  1. Add ' . implode(', ', $fields) . ' to *Inputs::TRANSLATED_ATTRIBUTES (or spread PublishableMetadata::PUBLISHABLE_ATTRIBUTES)');
            $this->line('  2. Remove those fields from *Inputs::FILLABLE');
            $this->line('  3. Cast published/datetime on the Translation model');
            $this->line('  4. Update create migration: published:false on main, PublishableMetadata::addColumns on translations');
            $this->line('  5. Update legacy migrators to write published onto translations');
        }

        $this->newLine();
        $this->info('Next: php artisan operations:process (or your modularous operations process command)');

        return self::SUCCESS;
    }

    /**
     * @param list<string> $fields
     */
    private function buildOperationContents(
        string $modelClass,
        string $mainTable,
        string $translationsTable,
        string $foreignKey,
        array $fields,
        string $moduleStudly,
        string $routeName,
    ): string {
        $tag = (string) $this->option('tag');
        $fieldsExport = $this->exportStringList($fields);
        $headline = Str::headline("{$moduleStudly} {$routeName} publishable to translations");

        return <<<PHP
<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use {$modelClass};
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;

/**
 * {$headline}
 *
 * Generated by modularous:publishable:make-translated.
 */
return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        \$this->output = new ConsoleOutput;
    }

    protected bool \$async = false;

    protected string \$queue = 'default';

    protected ?string \$tag = '{$tag}';

    public function process(): void
    {
        \$mainTable = '{$mainTable}';
        \$translationsTable = '{$translationsTable}';
        \$foreignKey = '{$foreignKey}';
        \$fields = {$fieldsExport};

        if (! Schema::hasTable(\$mainTable) || ! Schema::hasTable(\$translationsTable)) {
            \$this->output->writeln('Publishable move skipped: tables missing');

            return;
        }

        \$this->ensureTranslationColumns(\$translationsTable, \$fields);
        \$this->backfill(\$mainTable, \$translationsTable, \$foreignKey, \$fields);
        \$this->dropMainColumns(\$mainTable, \$fields);

        \$this->output->writeln('Moved publishable columns to ' . \$translationsTable);
    }

    /**
     * @param  list<string>  \$fields
     */
    private function ensureTranslationColumns(string \$translationsTable, array \$fields): void
    {
        \$toAdd = array_values(array_filter(
            \$fields,
            static fn (string \$column): bool => ! Schema::hasColumn(\$translationsTable, \$column),
        ));

        if (\$toAdd === []) {
            return;
        }

        Schema::table(\$translationsTable, function (Blueprint \$table) use (\$toAdd): void {
            foreach (\$toAdd as \$column) {
                if (\$column === 'published') {
                    \$table->boolean('published')->default(true);
                } else {
                    \$table->timestamp(\$column)->nullable();
                }
            }
        });

        \$this->output->writeln('Added on ' . \$translationsTable . ': ' . implode(', ', \$toAdd));
    }

    /**
     * @param  list<string>  \$fields
     */
    private function backfill(string \$mainTable, string \$translationsTable, string \$foreignKey, array \$fields): void
    {
        \$sourceFields = array_values(array_filter(
            \$fields,
            static fn (string \$column): bool => Schema::hasColumn(\$mainTable, \$column),
        ));

        if (\$sourceFields === []) {
            \$this->output->writeln('Backfill skipped: no publish columns on main table');

            return;
        }

        \$updated = 0;

        DB::table(\$mainTable)->orderBy('id')->chunkById(100, function (\$rows) use (
            \$translationsTable,
            \$foreignKey,
            \$sourceFields,
            &\$updated,
        ): void {
            foreach (\$rows as \$row) {
                \$payload = [];
                foreach (\$sourceFields as \$column) {
                    \$payload[\$column] = \$column === 'published'
                        ? (bool) (\$row->published ?? true)
                        : \$row->{\$column};
                }

                \$updated += DB::table(\$translationsTable)
                    ->where(\$foreignKey, \$row->id)
                    ->update(\$payload);
            }
        });

        \$this->output->writeln("Backfilled {\$updated} translation row(s)");
    }

    /**
     * @param  list<string>  \$fields
     */
    private function dropMainColumns(string \$mainTable, array \$fields): void
    {
        \$drop = array_values(array_filter(
            \$fields,
            static fn (string \$column): bool => Schema::hasColumn(\$mainTable, \$column),
        ));

        if (\$drop === []) {
            return;
        }

        Schema::table(\$mainTable, function (Blueprint \$table) use (\$drop): void {
            \$table->dropColumn(\$drop);
        });

        \$this->output->writeln('Dropped from ' . \$mainTable . ': ' . implode(', ', \$drop));
    }
};

PHP;
    }

    private function operationPath(string $moduleStudly, string $routeName): string
    {
        $directory = base_path(config('one-time-operations.directory', 'operations'));
        $slug = Str::snake("move_{$moduleStudly}_{$routeName}_publishable_to_translations");

        return $directory . DIRECTORY_SEPARATOR . $this->getDatePrefix() . '_' . $slug . '_operation.php';
    }

    /**
     * Short-array PHP literal for generated operation stubs (single line).
     *
     * @param list<string> $values
     */
    private function exportStringList(array $values): string
    {
        $quoted = array_map(
            static fn (string $value): string => "'" . str_replace("'", "\\'", $value) . "'",
            $values,
        );

        return '[' . implode(', ', $quoted) . ']';
    }

    private function getDatePrefix(): string
    {
        return Carbon::now()->format('Y_m_d_His');
    }

    /**
     * @param list<string> $fields
     */
    private function patchCodeFiles(string $modulePath, string $routeName, array $fields): void
    {
        $studly = Str::studly($routeName);
        $inputsPath = $modulePath . '/Support/' . $studly . 'Inputs.php';
        if (! File::exists($inputsPath)) {
            $inputsPath = $modulePath . '/Support/' . Str::studly((string) $this->argument('module')) . 'Inputs.php';
        }

        if (File::exists($inputsPath)) {
            $contents = File::get($inputsPath);
            if (! str_contains($contents, 'use Unusualify\\Modularous\\Support\\PublishableMetadata')) {
                if (str_contains($contents, 'use Unusualify\\Modularous\\Support\\TranslatableMetadata;')) {
                    $contents = str_replace(
                        'use Unusualify\\Modularous\\Support\\TranslatableMetadata;',
                        "use Unusualify\\Modularous\\Support\\PublishableMetadata;\nuse Unusualify\\Modularous\\Support\\TranslatableMetadata;",
                        $contents,
                    );
                } else {
                    $contents = preg_replace(
                        '/(namespace [^;]+;\s+)/',
                        "$1\nuse Unusualify\\Modularous\\Support\\PublishableMetadata;\n",
                        $contents,
                        1,
                    ) ?? $contents;
                }
            }

            if (! str_contains($contents, 'PublishableMetadata::PUBLISHABLE_ATTRIBUTES')) {
                $contents = preg_replace(
                    '/(public const TRANSLATED_ATTRIBUTES = \[\s*)/',
                    "$1...PublishableMetadata::PUBLISHABLE_ATTRIBUTES,\n        ",
                    $contents,
                    1,
                ) ?? $contents;
            }

            if (preg_match('/public const FILLABLE = \[.*?\];/s', $contents, $match) === 1) {
                $fillable = $match[0];
                foreach ($fields as $field) {
                    $fillable = preg_replace(
                        "/\n\s*'" . preg_quote($field, '/') . "',/",
                        "\n",
                        $fillable,
                    ) ?? $fillable;
                }
                $contents = str_replace($match[0], $fillable, $contents);
            }

            File::put($inputsPath, $contents);
            $this->info('Patched Inputs: ' . $inputsPath);
        } else {
            $this->warn('Inputs file not found; skipped code patch for Inputs.');
        }

        $translationPath = $modulePath . '/Entities/Translations/' . $studly . 'Translation.php';
        if (File::exists($translationPath)) {
            $contents = File::get($translationPath);
            if (! str_contains($contents, "'published' => 'boolean'")) {
                $contents = preg_replace(
                    "/(protected \\\$casts = \[\s*'active' => 'boolean',)/",
                    "$1\n        'published' => 'boolean',\n        'publish_start_date' => 'datetime',\n        'publish_end_date' => 'datetime',",
                    $contents,
                    1,
                ) ?? $contents;
                File::put($translationPath, $contents);
                $this->info('Patched Translation casts: ' . $translationPath);
            }
        } else {
            $this->warn('Translation model not found; skipped casts patch.');
        }
    }
}
