<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnusualDefaultRepeatersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $unusualRepeatersTable = unusualConfig('tables.repeaters', 'modularity_repeaters');

        if (! Schema::hasTable($unusualRepeatersTable)) {
            Schema::create($unusualRepeatersTable, function (Blueprint $table) {
                $table->{unusualIncrementsMethod()}('id');
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
        $unusualRepeatersTable = unusualConfig('tables.repeaters', 'modularity_repeaters');

        Schema::dropIfExists($unusualRepeatersTable);
    }
}
