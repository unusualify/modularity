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
        Schema::create(config('priceable.tables.vat_rates'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedFloat('rate');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('priceable.tables.currencies'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('symbol', 10)->nullable()->default(NULL);
            $table->string('iso_4217', 3)->default(null)->nullable();
            $table->integer('iso_4217_number')->default(null)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('priceable.tables.price_types'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('priceable.tables.prices'), function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('priceable');

            $table->unsignedBigInteger('price_type_id')->default(null)->nullable();

            $table->unsignedBigInteger('vat_rate_id');
            $table->unsignedBigInteger('currency_id');
            $table->bigInteger('display_price')->default(0);
            $table->bigInteger('price_excluding_vat')->default(0);
            $table->bigInteger('price_including_vat')->default(0);
            $table->bigInteger('vat_amount')->default(0);
            $table->timestamp('valid_from')->nullable()->default(null);
            $table->timestamp('valid_till')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vat_rate_id')->references('id')->on(config('priceable.tables.vat_rates'));
            $table->foreign('currency_id')->references('id')->on(config('priceable.tables.currencies'));
            $table->foreign('price_type_id')->references('id')->on(config('priceable.tables.price_types'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('priceable.tables.prices'));
        Schema::dropIfExists(config('priceable.tables.price_types'));
        Schema::dropIfExists(config('priceable.tables.currencies'));
        Schema::dropIfExists(config('priceable.tables.vat_rates'));
    }
};
