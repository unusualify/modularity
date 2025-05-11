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
        $paymentsTable = config('payable.table', 'up_payments');

        Schema::create($paymentsTable, function (Blueprint $table) {
            $table->id();
            $table->string('payment_gateway')->nullable();
            $table->string('order_id');
            $table->integer('amount');
            $table->string('currency', 3);
            $table->string('status')->default('PENDING');
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
        $paymentsTable = config('payable.table', 'up_payments');

        Schema::dropIfExists($paymentsTable);

    }
};
