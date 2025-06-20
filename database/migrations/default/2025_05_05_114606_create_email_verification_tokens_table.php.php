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
        $emailVerificationTokensTable = modularityConfig('tables.email_verification_tokens', 'um_email_verification_tokens');

        Schema::create($emailVerificationTokensTable, function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $emailVerificationTokensTable = modularityConfig('tables.email_verification_tokens', 'um_email_verification_tokens');
        Schema::dropIfExists($emailVerificationTokensTable);
    }
};

