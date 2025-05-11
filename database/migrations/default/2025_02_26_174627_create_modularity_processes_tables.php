<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableProcesses = modularityConfig('tables.processes', 'm_processes');
        $tableProcessHistories = modularityConfig('tables.process_histories', 'm_process_histories');

        if (! Schema::hasTable($tableProcesses)) {
            Schema::create($tableProcesses, function (Blueprint $table) {
                // this will create an id, name field
                createDefaultTableFields($table);

                $table->uuidMorphs('processable');
                $table->string('status');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable($tableProcessHistories)) {
            Schema::create($tableProcessHistories, function (Blueprint $table) use ($tableProcesses) {
                // this will create an id, name field
                createDefaultTableFields($table);

                $table->foreignId('process_id')
                    ->constrained($tableProcesses)
                    ->onDelete('cascade');

                $table->string('status');
                $table->text('reason')->nullable();

                $table->uuid('user_id')
                    ->nullable();

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        $tableProcesses = modularityConfig('tables.processes', 'm_processes');
        $tableProcessHistories = modularityConfig('tables.process_histories', 'm_process_histories');

        Schema::dropIfExists($tableProcessHistories);
        Schema::dropIfExists($tableProcesses);
    }
};
