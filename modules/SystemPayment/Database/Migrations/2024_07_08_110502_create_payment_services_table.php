<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Facades\MigrationBackup;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('payment_services', function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('name')->unique();
            $table->string('key')->unique();
            $table->decimal('transaction_fee_percentage', 5, 2)->default(0.00);
            $table->boolean('is_external')->default(false);
            $table->boolean('is_internal')->default(false);
            $table->string('button_style')->nullable();

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });
        Schema::enableForeignKeyConstraints();

        MigrationBackup::restore();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        MigrationBackup::backup('payment_services');
        Schema::dropIfExists('payment_services');
        Schema::enableForeignKeyConstraints();
    }
};
