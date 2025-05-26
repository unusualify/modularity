<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Oobook\Priceable\Models\Price;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $priceTableName = config('priceable.tables.prices', 'unfy_prices');
        $currencyTableName = config('priceable.tables.currencies', 'unfy_currencies');

        Schema::table(config('payable.table', 'up_payments'), function (Blueprint $table) use ($priceTableName, $currencyTableName) {
            $table->foreignId('payment_service_id')->after('id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('price_id')->after('payment_service_id')->constrained($priceTableName);
            $table->foreignId('currency_id')->after('price_id')->constrained($currencyTableName);
        });

        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('payable.table', 'up_payments'), function (Blueprint $table) {
            $table->dropForeign(['payment_service_id']);
            $table->dropColumn('payment_service_id');
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });

    }
};
