<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Ensure builtin singleton revisions table exists for installs that already ran
 * {@see 2025_02_07_122316_create_modularous_singletons_table} before revisions were added.
 *
 * Same schema as the updated singletons migration:
 * {@see createDefaultRevisionsTableFields} with FK singleton_id → tables.singletons.
 */
return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput;
    }

    protected bool $async = false;

    protected string $queue = 'default';

    protected ?string $tag = 'singleton-revision';

    public function process(): void
    {
        $singletonsTable = Modularous::config('tables.singletons', 'modularous_singletons');
        $revisionsTable = Modularous::config('tables.singleton_revisions', 'modularous_singleton_revisions');

        if (Schema::hasTable($revisionsTable)) {
            $this->output->writeln("Modularous: {$revisionsTable} already exists — skipped");

            return;
        }

        if (! Schema::hasTable($singletonsTable)) {
            $this->output->writeln("Modularous: {$singletonsTable} missing — cannot create {$revisionsTable}");

            return;
        }

        Schema::create($revisionsTable, function (Blueprint $table) use ($singletonsTable): void {
            createDefaultRevisionsTableFields($table, 'singleton', $singletonsTable);
        });

        $this->output->writeln("Modularous: created {$revisionsTable} (singleton_id → {$singletonsTable})");
    }
};
