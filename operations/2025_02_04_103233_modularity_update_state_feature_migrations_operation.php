<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;
use Unusualify\Modularity\Facades\Modularity;

use function Laravel\Prompts\confirm;
return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput;
    }

    /**
     * Determine if the operation is being processed asynchronously.
     */
    protected bool $async = false;

    /**
     * The queue that the job will be dispatched to.
     */
    protected string $queue = 'default';

    /**
     * A tag name, that this operation can be filtered by.
     */
    protected ?string $tag = 'modularity';

    /**
     * Process the operation.
     */
    public function process(): void
    {
        $stateTable = Modularity::config('tables.states', 'modularity_states');
        $stateTranslationsTable = Modularity::config('tables.state_translations', 'modularity_state_translations');
        $stateablesTable = Modularity::config('tables.stateables', 'modularity_stateables');


        // 2024_10_22_093259_create_stateables_table.php
        // 2024_10_22_093258_create_states_table.php
        DB::table('migrations')
            // ->where('migration', 'like', '%create_states_table%')
            ->where('migration', '2024_10_22_093258_create_states_table')
            ->update(['migration' => '2024_10_22_093258_create_modularity_stateable_tables']);

        DB::table('migrations')
            ->where('migration', '2024_10_22_093259_create_stateables_table')
            ->delete();

        if (! Schema::hasTable($stateTable)) {

            $stateTableExists = Schema::hasTable('states');
            $stateTranslationsTableExists = Schema::hasTable('state_translations');
            $stateablesTableExists = Schema::hasTable('stateables');

            if($stateTableExists && $stateTranslationsTableExists && $stateablesTableExists && confirm('Do you want to rename the states, state_translations and stateables tables to ' . $stateTable . ', ' . $stateTranslationsTable . ' and ' . $stateablesTable . '?')) {

                Schema::rename('states', $stateTable);
                Schema::rename('state_translations', $stateTranslationsTable);
                Schema::rename('stateables', $stateablesTable);

                $this->info("\tstates table changed as " . $stateTable);
                $this->info("\tstate_translations table changed as " . $stateTranslationsTable);
                $this->info("\tstateables table changed as " . $stateablesTable);
                $this->output->writeln('');
            } else {
                Artisan::call('migrate', [
                    '--path' => Modularity::getVendorPath('database/migrations/default/2024_10_22_093258_create_modularity_stateable_tables.php'),
                ]);

                $this->info("\t" . $stateTable . " migrations created");
            }
        }

        if (Schema::hasTable($stateTranslationsTable)) {
            try {
                Schema::table($stateTranslationsTable, function($table) use ($stateTable, $stateTranslationsTable) {
                    // Get the current foreign key reference table
                    $currentForeignKey = DB::select("
                        SELECT REFERENCED_TABLE_NAME
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE TABLE_NAME = ?
                        AND CONSTRAINT_NAME = ?
                        AND TABLE_SCHEMA = ?",
                        [$stateTranslationsTable, 'fk_state_translations_state_id', DB::getDatabaseName()]
                    );
                    // Only update if the foreign key points to a different table
                    if (!empty($currentForeignKey) && $currentForeignKey[0]->REFERENCED_TABLE_NAME !== $stateTable) {
                        $table->dropForeign('fk_state_translations_state_id');
                        $table->foreign('state_id', 'fk_state_translations_state_id')
                            ->references('id')
                            ->on($stateTable)
                            ->onDelete('CASCADE');
                    }
                });
            } catch (\Exception $e) {
                $this->error("Could not update foreign key: " . $e->getMessage());
            }
        }

        $this->output->writeln('Modularity: Update State Feature Migrations operation processed');
    }
};
