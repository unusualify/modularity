<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $demandTable = config('modularity.tables.demands', 'um_demands');

        Schema::create($demandTable, function (Blueprint $table) use ($demandTable) {
            // this will create an id, name field
            createDefaultTableFields($table);

            $table->uuidMorphs('demandable');
            $table->uuidMorphs('demander');
            $table->nullableUuidMorphs('responder'); //

            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->string('title');
            $table->text('description');
            $table->text('response')->nullable();

            $table->timestamp('due_at')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // For Q&A thread functionality
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on($demandTable)->onDelete('cascade');

            // Indexes for better performance
            $table->index(['status', 'priority']);
            $table->index('due_at');
            $table->index('parent_id');

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table, false, false);
        });
    }

    public function down()
    {
        $demandTable = config('modularity.tables.demands', 'um_demands');
        Schema::dropIfExists($demandTable);
    }
};
