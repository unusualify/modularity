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
        $paymentsTable = config('payment.table', 'umod_payments');
        // dd($paymentsTable);
        Schema::create($paymentsTable, function (Blueprint $table) {
            $table->id();
            $table->string('payment_gateway')->nullable();
            $table->string('order_id');
            $table->integer('amount');
            $table->integer('currency_id')->nullable();
            $table->enum('status', ['PENDING','CANCELLED','COMPLETED','FAILED','REFUNDED']);
            $table->string('email')->nullable();
            $table->integer('installment')->nullable();
            $table->json('parameters')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('payable.tables.payments', 'umod_payments'));


    }
};
