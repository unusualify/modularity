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
        Schema::table(config('priceable.tables.prices'), function (Blueprint $table) {
            $table->after('price_including_vat', function (Blueprint $table) {
                $table->bigInteger('raw_price')->default(0);
                $table->decimal('discount_percentage', 5, 2)->default(0.00);

            });
        });

        // Schema::table(config('priceable.tables.prices'), function (Blueprint $table) {
        //     $table->dropColumn('display_price');
        //     $table->dropColumn('price_excluding_vat');
        //     $table->dropColumn('price_including_vat');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        // Schema::table(config('priceable.tables.prices'), function (Blueprint $table) {
        //     $table->after('currency_id', function (Blueprint $table) {
        //         $table->bigInteger('display_price')->default(0);
        //         $table->bigInteger('price_excluding_vat')->default(0);
        //         $table->bigInteger('price_including_vat')->default(0);
        //     });
        // });

        Schema::table(config('priceable.tables.prices'), function (Blueprint $table) {
            $table->dropColumn('raw_price');
            $table->dropColumn('discount_percentage');
        });


    }
};
