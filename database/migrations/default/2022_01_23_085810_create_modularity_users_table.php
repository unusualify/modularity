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
        $adminUserTable = modularityConfig('tables.users', 'um_users');
        $companyTable = modularityConfig('tables.companies', 'modularity_companies');

        if (! Schema::hasTable($adminUserTable)) {
            Schema::create(modularityConfig('tables.users', 'um_users'), function (Blueprint $table) use ($companyTable) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable(); // Foreign key column
                $table->string('name');
                $table->string('surname', 30)->nullable();
                $table->string('job_title')->nullable();
                $table->boolean('published')->default(true);
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('language')->default('en');
                $table->string('timezone')->default('Europe/London');
                $table->string('phone', 20)->nullable();
                $table->integer('country_id')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->foreign('company_id')
                    ->references('id')
                    ->on($companyTable)
                    ->cascadeOnUpdate()
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        $adminUserTable = modularityConfig('tables.users', 'um_users');

        if (Schema::hasTable($adminUserTable)) {
            Schema::dropIfExists(modularityConfig('tables.users', 'um_users'));
        }
    }
};
