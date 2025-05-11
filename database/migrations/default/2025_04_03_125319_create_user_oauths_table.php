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
        $usersTable = modularityConfig('tables.users', 'um_users');

        Schema::create($userOauthTable, function (Blueprint $table) use ($usersTable) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('token')->index();
            $table->string('provider')->index();
            $table->longText('avatar')->nullable();
            $table->string('oauth_id')->index();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on($usersTable)->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $userOauthTable = modularityConfig('tables.user_oauths', 'um_user_oauths');
        Schema::dropIfExists($userOauthTable);
    }
};
