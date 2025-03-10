<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $assignmentTable = config('modularity.tables.assignments', 'm_assignments');

        Schema::create($assignmentTable, function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);

            $table->uuidMorphs('assignable');
            $table->uuidMorphs('assignee');
            $table->uuidMorphs('assigner');

            $table->string('status')->default('pending');
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->dateTime('accepted_at')->nullable();
            $table->timestamp('due_at');
            $table->dateTime('completed_at')->nullable();

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table, false, false);
        });
    }

    public function down()
    {
        $assignmentTable = config('modularity.tables.assignments', 'm_assignments');
        Schema::dropIfExists($assignmentTable);
    }
};
