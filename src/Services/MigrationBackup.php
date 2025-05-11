<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MigrationBackup
{
    protected string $backupKey;

    protected string $caller;

    protected string $table;

    protected array $schemaSnapshot = [];

    protected bool $constraintsDisabled = false;

    protected array $schemaChanges = [];

    protected function generateBackupKey(string $table): string
    {
        return 'migration_backup_' . Str::slug($this->caller) . '_' . Str::slug($table);
    }

    protected function getSchemaChangesKey(): string
    {
        return 'schema_changes_' . Str::slug($this->caller);
    }

    protected function getMigrationBackupsKey(): string
    {
        return 'migration_backups_' . Str::slug($this->caller);
    }

    protected function initializeContext(): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $this->caller = basename($trace[2]['file'], '.php');
        $this->schemaChanges = Cache::get($this->getSchemaChangesKey(), []);
    }

    /**
     * Get foreign keys information using Laravel's Schema
     */
    protected function getForeignKeys(string $table): array
    {
        $driver = DB::connection()->getDriverName();
        $foreignKeys = [];

        try {
            if ($driver === 'sqlite') {
                // Get outgoing foreign keys
                $outgoing = DB::select("PRAGMA foreign_key_list({$table})");
                foreach ($outgoing as $fk) {
                    \Log::info("Found outgoing foreign key in {$table}:", (array) $fk);
                    $foreignKeys[] = [
                        'name' => "fk_{$table}_{$fk->from}",
                        'local_column' => $fk->from,
                        'foreign_table' => $fk->table,
                        'foreign_column' => $fk->to,
                    ];
                }

                // Get incoming foreign keys
                $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
                foreach ($tables as $otherTable) {
                    if ($otherTable === $table) {
                        continue;
                    }

                    $incoming = DB::select("PRAGMA foreign_key_list({$otherTable})");
                    foreach ($incoming as $fk) {
                        if ($fk->table === $table) {
                            \Log::info("Found incoming foreign key to {$table} from {$otherTable}:", (array) $fk);
                            $foreignKeys[] = [
                                'name' => "fk_{$otherTable}_{$fk->from}",
                                'local_column' => $fk->to,
                                'foreign_table' => $otherTable,
                                'foreign_column' => $fk->from,
                            ];
                        }
                    }
                }
            }
            // ... rest of the database drivers ...
        } catch (\Exception $e) {
            \Log::error("Error getting foreign keys for {$table}: " . $e->getMessage());
        }

        \Log::info("All foreign keys for {$table}:", $foreignKeys);

        return $foreignKeys;
    }

    protected function getTableSchema(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $types = [];
        $driver = DB::connection()->getDriverName();

        foreach ($columns as $column) {
            switch ($driver) {
                case 'mysql':
                    $columnInfo = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column])[0];
                    $types[$column] = [
                        'type' => $columnInfo->Type,
                        'nullable' => $columnInfo->Null === 'YES',
                        'default' => $columnInfo->Default,
                    ];

                    break;

                case 'pgsql':
                    $columnInfo = DB::select('
                        SELECT column_name, data_type, is_nullable, column_default
                        FROM information_schema.columns
                        WHERE table_name = ? AND column_name = ?
                    ', [$table, $column])[0];
                    $types[$column] = [
                        'type' => $columnInfo->data_type,
                        'nullable' => $columnInfo->is_nullable === 'YES',
                        'default' => $columnInfo->column_default,
                    ];

                    break;

                case 'sqlite':
                    $columnInfo = DB::select("PRAGMA table_info({$table})");
                    $columnData = collect($columnInfo)->firstWhere('name', $column);
                    $types[$column] = [
                        'type' => $columnData->type,
                        'nullable' => ! $columnData->notnull,
                        'default' => $columnData->dflt_value,
                    ];

                    break;
            }
        }

        return [
            'columns' => $types,
            'foreign_keys' => $this->getForeignKeys($table),
        ];
    }

    /**
     * Take snapshot of current schema
     */
    protected function takeSchemaSnapshot(string $table): void
    {
        $this->schemaSnapshot[$table] = $this->getTableSchema($table);

        // Store initial schema if not exists
        if (! isset($this->schemaChanges[$table]['initial'])) {
            $this->schemaChanges[$table]['initial'] = $this->schemaSnapshot[$table];
        }
    }

    /**
     * Track schema changes
     */
    protected function trackSchemaChanges(string $table): void
    {
        // Load schema snapshot from backup if not already set
        if (empty($this->schemaSnapshot[$table])) {
            $backup = Cache::get($this->generateBackupKey($table));
            if ($backup && isset($backup['tables'][$table]['schema'])) {
                $this->schemaSnapshot[$table] = $backup['tables'][$table]['schema'];
            } else {
                return;
            }
        }

        $currentSchema = $this->getTableSchema($table);
        $previousSchema = $this->schemaSnapshot[$table];

        // Debug
        \Log::info('Previous schema:', ['columns' => array_keys($previousSchema['columns'])]);
        \Log::info('Current schema:', ['columns' => array_keys($currentSchema['columns'])]);

        $changes = [
            'added' => array_diff_key(
                $currentSchema['columns'],
                $previousSchema['columns']
            ),
            'removed' => array_diff_key(
                $previousSchema['columns'],
                $currentSchema['columns']
            ),
            'modified' => array_filter(
                $currentSchema['columns'],
                function ($column, $name) use ($previousSchema) {
                    return isset($previousSchema['columns'][$name]) &&
                           $previousSchema['columns'][$name] !== $column;
                },
                ARRAY_FILTER_USE_BOTH
            ),
        ];

        // Debug
        \Log::info('Detected changes:', $changes);

        // Store changes if any found
        if (! empty(array_filter($changes))) {
            $timestamp = now()->toDateTimeString();
            $this->schemaChanges[$table]['changes'][$timestamp] = $changes;
            $this->schemaChanges[$table]['current'] = $currentSchema;

            Cache::put($this->getSchemaChangesKey(), $this->schemaChanges);
        }
    }

    /**
     * Disable foreign key constraints
     */
    protected function disableConstraints(): void
    {
        if ($this->constraintsDisabled) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                break;
            case 'pgsql':
                DB::statement('SET CONSTRAINTS ALL DEFERRED');

                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys=OFF');

                break;
        }

        $this->constraintsDisabled = true;
    }

    /**
     * Enable foreign key constraints
     */
    protected function enableConstraints(): void
    {
        if (! $this->constraintsDisabled) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=1');

                break;
            case 'pgsql':
                DB::statement('SET CONSTRAINTS ALL IMMEDIATE');

                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys=ON');

                break;
        }

        $this->constraintsDisabled = false;
    }

    /**
     * Backup table data with schema snapshot
     */
    public function backup(string $table, ?array $columns = null, bool $includeRelated = true): void
    {
        $this->initializeContext();
        $this->table = $table;
        $this->backupKey = $this->generateBackupKey($table);
        $backupData = [];

        if (Schema::hasTable($table)) {
            // Debug: Log the start of backup process
            \Log::info("Starting backup for table: {$table}");

            $this->takeSchemaSnapshot($table);

            // Debug: Log the schema snapshot and foreign keys
            \Log::info("Schema snapshot for {$table}:", [
                'columns' => array_keys($this->schemaSnapshot[$table]['columns']),
                'foreign_keys' => $this->schemaSnapshot[$table]['foreign_keys'],
            ]);

            $query = DB::table($table);
            if ($columns) {
                $query->select($columns);
            }

            $backupData[$table] = [
                'data' => $query->get()->toArray(),
                'schema' => $this->schemaSnapshot[$table],
            ];

            if ($includeRelated) {
                // Debug: Process related tables
                \Log::info("Processing related tables for {$table}");

                // Get both incoming and outgoing relationships
                $relatedTables = collect($this->schemaSnapshot[$table]['foreign_keys'])
                    ->pluck('foreign_table')
                    ->unique()
                    ->values()
                    ->all();

                \Log::info('Found related tables:', $relatedTables);

                foreach ($relatedTables as $relatedTable) {
                    \Log::info("Processing related table: {$relatedTable}");

                    if (! isset($backupData[$relatedTable]) && Schema::hasTable($relatedTable)) {
                        $this->takeSchemaSnapshot($relatedTable);
                        $backupData[$relatedTable] = [
                            'data' => DB::table($relatedTable)->get()->toArray(),
                            'schema' => $this->schemaSnapshot[$relatedTable],
                        ];
                        \Log::info("Added related table {$relatedTable} to backup");
                    }
                }
            }
        }

        // Debug: Log final backup data structure
        \Log::info('Final backup data structure:', [
            'tables' => array_keys($backupData),
            'main_table' => $table,
        ]);

        Cache::put($this->backupKey, [
            'main_table' => $table,
            'tables' => $backupData,
        ]);

        $migrationBackups = Cache::get($this->getMigrationBackupsKey(), []);
        $migrationBackups[$table] = $this->backupKey;
        Cache::put($this->getMigrationBackupsKey(), $migrationBackups);
    }

    /**
     * Restore data from backup
     */
    public function restore(?string $table = null): bool
    {
        $this->initializeContext();

        try {
            $this->disableConstraints();
            $success = true;

            if ($table) {
                // Restore specific table
                $this->backupKey = $this->generateBackupKey($table);

                $success = $this->restoreTable($this->backupKey);
            } else {
                // Restore all tables in this migration
                $migrationBackups = Cache::get($this->getMigrationBackupsKey(), []);
                foreach ($migrationBackups as $table => $backupKey) {
                    $success = $this->restoreTable($backupKey) && $success;
                }
            }

            return $success;
        } finally {
            $this->enableConstraints();
        }
    }

    protected function restoreTable(string $backupKey): bool
    {
        $backup = Cache::get($backupKey);

        if (! $backup) {
            return false;
        }

        foreach ($backup['tables'] as $table => $tableData) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->trackSchemaChanges($table);

            foreach ($tableData['data'] as $record) {
                $record = (array) $record;
                $this->restoreRecord($table, $record);
            }
        }

        foreach ($backup['tables'] as $table => $tableData) {
            $this->clearBackup($table);
        }

        return true;
    }

    /**
     * Restore a single record
     */
    protected function restoreRecord(string $table, array $record): void
    {
        $currentSchema = $this->getTableSchema($table);
        $validColumns = array_keys($currentSchema['columns']);

        // Filter out invalid columns
        $record = array_intersect_key($record, array_flip($validColumns));

        // Set defaults for new columns
        foreach ($currentSchema['columns'] as $column => $info) {
            if (! isset($record[$column])) {
                $record[$column] = $this->getDefaultValueForType($info['type']);
            }
        }

        $exists = DB::table($table)
            ->where('id', $record['id'])
            ->exists();

        if ($exists) {
            DB::table($table)
                ->where('id', $record['id'])
                ->update($record);
        } else {
            DB::table($table)->insert($record);
        }
    }

    /**
     * Get default value based on MySQL column type
     */
    protected function getDefaultValueForType(string $type): mixed
    {
        // Extract base type without length/precision
        $baseType = mb_strtolower(preg_replace('/\(.*\)/', '', $type));

        return match ($baseType) {
            'json', 'text', 'varchar', 'char' => '',
            'int', 'bigint', 'smallint', 'tinyint' => 0,
            'decimal', 'float', 'double' => 0.0,
            'boolean', 'tinyint(1)' => false,
            'datetime', 'timestamp', 'date' => null,
            default => null,
        };
    }

    /**
     * Get schema changes
     */
    protected function getSchemaChanges(string $table): array
    {
        // Load schema snapshot from backup if not already set
        if (empty($this->schemaSnapshot[$table])) {
            $backup = Cache::get($this->generateBackupKey($table));
            if ($backup && isset($backup['tables'][$table]['schema'])) {
                $this->schemaSnapshot[$table] = $backup['tables'][$table]['schema'];
            } else {
                return ['added' => [], 'removed' => [], 'modified' => []];
            }
        }

        $currentSchema = $this->getTableSchema($table);
        $previousSchema = $this->schemaSnapshot[$table];

        return [
            'added' => array_diff_key($currentSchema['columns'], $previousSchema['columns']),
            'removed' => array_diff_key($previousSchema['columns'], $currentSchema['columns']),
            'modified' => array_filter(
                array_intersect_key($currentSchema['columns'], $previousSchema['columns']),
                fn ($column, $info) => $previousSchema['columns'][$column] !== $info,
                ARRAY_FILTER_USE_BOTH
            ),
        ];
    }

    /**
     * Get schema history
     */
    public function getSchemaHistory(?string $table = null): array
    {
        $this->initializeContext();
        if ($table) {
            return $this->schemaChanges[$table] ?? [];
        }

        return $this->schemaChanges;
    }

    /**
     * Get backup data
     */
    public function getBackup(?string $table = null): ?array
    {
        $this->initializeContext();

        if ($table) {
            $this->backupKey = $this->generateBackupKey($table);

            return Cache::get($this->backupKey);
        }

        $migrationBackups = Cache::get($this->getMigrationBackupsKey(), []);
        $allBackups = [];

        foreach ($migrationBackups as $table => $backupKey) {
            $allBackups[$table] = Cache::get($backupKey);
        }

        return $allBackups;
    }

    /**
     * Clear backup
     */
    public function clearBackup(?string $table = null): void
    {
        $this->initializeContext();

        if ($table) {
            Cache::forget($this->generateBackupKey($table));
        } else {
            $migrationBackups = Cache::get($this->getMigrationBackupsKey(), []);
            foreach ($migrationBackups as $backupKey) {
                Cache::forget($backupKey);
            }
            Cache::forget($this->getMigrationBackupsKey());
        }

        if (! $table || empty(Cache::get($this->getMigrationBackupsKey(), []))) {
            Cache::forget($this->getSchemaChangesKey());
        }
    }

    public function __destruct()
    {
        $this->enableConstraints();
    }
}
