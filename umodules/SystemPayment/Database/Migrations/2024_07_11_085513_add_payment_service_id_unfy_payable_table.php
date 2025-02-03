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

        $price = new Price;
        $priceTableName = $price->getTable();
        // dd(config('payable.table', 'umod_payments'));
        Schema::table(config('payable.table', 'unfy_payments'), function (Blueprint $table) use ($priceTableName) {

            $table->foreignId('payment_service_id')->after('id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('price_id')->after('payment_service_id')->constrained($priceTableName);
            $table->integer('currency_id')->nullable()->change();
        });

        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      
        Schema::table(config('payable.table', 'unfy_payments'), function (Blueprint $table) {
            $table->dropForeign(['payment_service_id']);
            $table->dropColumn('payment_service_id');
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
            $table->integer('currency_id')->nullable(false)->change();
        });

    }
};
