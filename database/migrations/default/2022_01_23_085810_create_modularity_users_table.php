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
        $adminUserTable = modularityConfig('tables.users', 'admin_users');

        if (! Schema::hasTable($adminUserTable)) {
            Schema::create(modularityConfig('tables.users', 'admin_users'), function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('published')->default(false);
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        $adminUserTable = modularityConfig('tables.users', 'admin_users');

        if (Schema::hasTable($adminUserTable)) {
            Schema::dropIfExists(modularityConfig('tables.users', 'admin_users'));
        }
    }
};
