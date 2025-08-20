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
        $userOauthTable = modularityConfig('tables.user_oauths', 'um_user_oauths');

        Schema::table($userOauthTable, function (Blueprint $table) {
            $table->text('token')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $userOauthTable = modularityConfig('tables.user_oauths', 'um_user_oauths');
        Schema::table($userOauthTable, function (Blueprint $table) {
            $table->string('token')->nullable(false)->change();
        });
    }
};
