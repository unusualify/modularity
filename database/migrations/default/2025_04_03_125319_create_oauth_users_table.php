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
        Schema::create('user_oauths', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('token')->index();
            $table->string('provider')->index();
            $table->longText('avatar')->nullable();
            $table->string('oauth_id')->index();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('admin_users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_oauths');
    }
};
