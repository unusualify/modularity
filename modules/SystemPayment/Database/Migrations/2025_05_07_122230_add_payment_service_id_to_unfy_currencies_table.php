<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('priceable.tables.currencies', 'unfy_currencies'), function (Blueprint $table) {
            $table->after('id', function () use ($table) {
                $table->bigInteger('payment_service_id')->nullable();
            });

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('priceable.tables.currencies', 'unfy_currencies'), function (Blueprint $table) {
            $table->dropColumn('payment_service_id');
        });
    }
};
