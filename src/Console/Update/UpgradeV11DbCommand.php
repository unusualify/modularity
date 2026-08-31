<?php

namespace Unusualify\Modularous\Console\Update;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Telescope\Telescope;
use Unusualify\Modularous\Console\BaseCommand;

/**
 * Idempotent Modularity → Modularous DB namespace / guard / migration rename.
 *
 * Uses LOCATE (not LIKE) so namespaces containing "\" match on MySQL.
 *
 * @see packages/modularous/upgrades/v11.php Step 5
 */
class UpgradeV11DbCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:upgrade:v11-db
        {--dry-run : Count and report matches without writing}';

    protected $aliases = [
        'mod:upgrade:v11-db',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade DB records from Modularity to Modularous (namespace, guard_name, migrations)';

    private const NS_OLD = 'Unusualify\\Modularity';

    private const NS_NEW = 'Unusualify\\Modularous';

    /** JSON-encoded namespace (two backslashes in stored string). */
    private const JSON_NS_OLD = 'Unusualify\\\\Modularity';

    private const JSON_NS_NEW = 'Unusualify\\\\Modularous';

    private int $updated = 0;

    private int $skipped = 0;

    public function handle(): int
    {
        $telescope = class_exists(Telescope::class);

        if ($telescope) {
            Telescope::stopRecording();
        }

        try {
            return $this->runUpgrade();
        } finally {
            if ($telescope) {
                Telescope::startRecording();
            }
        }
    }

    private function runUpgrade(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun
            ? 'Modularity → Modularous DB upgrade (dry-run)'
            : 'Modularity → Modularous DB upgrade');

        $this->fixMigrations($dryRun);
        $this->fixActivityLog($dryRun);
        $this->fixModelMorphColumns($dryRun);
        $this->fixGuardNames($dryRun);
        $this->fixTelescope($dryRun);
        $this->fixUmTables($dryRun);

        $this->newLine();
        $this->info(sprintf(
            'Done. updated=%d skipped=%d%s',
            $this->updated,
            $this->skipped,
            $dryRun ? ' (dry-run)' : ''
        ));

        return self::SUCCESS;
    }

    private function fixMigrations(bool $dryRun): void
    {
        $this->line('▶ migrations');

        if (! Schema::hasTable('migrations')) {
            $this->skip('Table migrations not found.');

            return;
        }

        $rows = DB::table('migrations')
            ->where('migration', 'like', '%modularity%')
            ->pluck('migration');

        if ($rows->isEmpty()) {
            $this->skip('No modularity entries in migrations table.');

            return;
        }

        foreach ($rows as $name) {
            $newName = str_replace('modularity', 'modularous', $name);
            if ($newName === $name) {
                continue;
            }

            if (! $dryRun) {
                DB::table('migrations')
                    ->where('migration', $name)
                    ->update(['migration' => $newName]);
            }

            $this->ok("migrations: {$name} → {$newName}");
            $this->updated++;
        }
    }

    private function fixActivityLog(bool $dryRun): void
    {
        $this->line('▶ sp_activity_log');

        if (! Schema::hasTable('sp_activity_log')) {
            $this->skip('Table sp_activity_log not found.');

            return;
        }

        foreach (['subject_type', 'causer_type'] as $col) {
            $this->dbReplace('sp_activity_log', $col, self::NS_OLD, self::NS_NEW, $dryRun);
        }

        foreach (['properties', 'description'] as $col) {
            if (! Schema::hasColumn('sp_activity_log', $col)) {
                $this->skip("Column sp_activity_log.{$col} not found.");

                continue;
            }
            $this->dbReplace('sp_activity_log', $col, self::JSON_NS_OLD, self::JSON_NS_NEW, $dryRun);
            $this->dbReplace('sp_activity_log', $col, self::NS_OLD, self::NS_NEW, $dryRun);
        }
    }

    private function fixModelMorphColumns(bool $dryRun): void
    {
        $this->line('▶ sp_model_has_permissions & sp_model_has_roles');

        foreach (['sp_model_has_permissions', 'sp_model_has_roles'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->skip("Table {$table} not found.");

                continue;
            }
            $this->dbReplace($table, 'model_type', self::NS_OLD, self::NS_NEW, $dryRun);
        }
    }

    private function fixGuardNames(bool $dryRun): void
    {
        $this->line('▶ guard_name');

        foreach (['sp_roles', 'sp_permissions', 'um_creator_records'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'guard_name')) {
                $this->skip("Table/column {$table}.guard_name not found.");

                continue;
            }
            $this->dbReplace($table, 'guard_name', 'modularity', 'modularous', $dryRun);
        }
    }

    private function fixTelescope(bool $dryRun): void
    {
        $this->line('▶ Laravel Telescope');

        if (Schema::hasTable('telescope_entries')) {
            foreach (['content', 'family_hash'] as $col) {
                if (! Schema::hasColumn('telescope_entries', $col)) {
                    $this->skip("Column telescope_entries.{$col} not found.");

                    continue;
                }
                $this->dbReplace('telescope_entries', $col, self::JSON_NS_OLD, self::JSON_NS_NEW, $dryRun);
                $this->dbReplace('telescope_entries', $col, self::NS_OLD, self::NS_NEW, $dryRun);
            }
        } else {
            $this->skip('telescope_entries not found.');
        }

        foreach (['telescope_entries_tags', 'telescope_monitoring'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tag')) {
                $this->skip("Table/column {$table}.tag not found.");

                continue;
            }
            $this->dbReplace($table, 'tag', self::JSON_NS_OLD, self::JSON_NS_NEW, $dryRun);
            $this->dbReplace($table, 'tag', self::NS_OLD, self::NS_NEW, $dryRun);
        }
    }

    private function fixUmTables(bool $dryRun): void
    {
        $this->line('▶ um_* namespace / type columns');

        $umMap = [
            'um_authorizations' => ['authorized_type'],
            'um_spreads' => ['spreadable_type'],
            'um_tagged' => ['taggable_type'],
            'um_tags' => ['namespace'],
            'um_creator_records' => ['creator_type', 'creatable_type'],
        ];

        foreach ($umMap as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $this->skip("Table {$table} not found.");

                continue;
            }

            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    $this->skip("Column {$table}.{$col} not found.");

                    continue;
                }
                $this->dbReplace($table, $col, self::NS_OLD, self::NS_NEW, $dryRun);
            }
        }
    }

    /**
     * Replace substrings using LOCATE so "\" in namespaces is not treated as LIKE escape.
     */
    private function dbReplace(string $table, string $column, string $from, string $to, bool $dryRun): void
    {
        $count = (int) DB::table($table)
            ->whereRaw("LOCATE(?, `{$column}`) > 0", [$from])
            ->count();

        if ($count === 0) {
            return;
        }

        if (! $dryRun) {
            DB::update(
                "UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, ?, ?) WHERE LOCATE(?, `{$column}`) > 0",
                [$from, $to, $from]
            );
        }

        $display = mb_strlen($from) > 45 ? mb_substr($from, 0, 42) . '…' : $from;
        $this->ok(sprintf('%s.%s — %d row(s)  [%s → …]%s', $table, $column, $count, $display, $dryRun ? ' (dry-run)' : ''));
        $this->updated += $count;
    }

    private function ok(string $message): void
    {
        $this->line("    <fg=green>✔</> {$message}");
    }

    private function skip(string $message): void
    {
        $this->line("    <fg=yellow>–</> {$message}");
        $this->skipped++;
    }
}
