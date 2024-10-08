<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $pricesTable = config('priceable.prices', 'unfy_prices');

        if (! Schema::hasTable($pricesTable)) {
            Schema::create($pricesTable, function (Blueprint $table) {
                $table->boolean('is_payable')->default(false);
            });
        }
    }

    public function down()
    {
        $pricesTable = config('priceable.prices', 'unfy_prices');

        Schema::dropIfExists($pricesTable);
    }
};
