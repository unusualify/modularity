<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $repeatersTable = modularityConfig('tables.repeaters', 'modularity_repeaters');

        if (! Schema::hasTable($repeatersTable)) {
            Schema::create($repeatersTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                // $table->string('repeatable_type')->nullable(); // MODEL CLASS
                // $table->string('repeatable_id')->nullable(); // ID belonging to repeatable_type
                $table->uuidMorphs('repeatable');
                $table->json('content');
                $table->string('role')->nullable(); // input name
                $table->string('locale', 6)->index();
                $table->timestamps();
                $table->softDeletes();
                // $table->index(['repeatable_type', 'repeatable_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $repeatersTable = modularityConfig('tables.repeaters', 'modularity_repeaters');

        Schema::dropIfExists($repeatersTable);
    }
};
