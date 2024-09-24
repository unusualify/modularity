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
        $companyTableName = unusualConfig('tables.companies', 'modularity_companies');
        Schema::table( unusualConfig('tables.users', 'admin_users'), function (Blueprint $table) use($companyTableName) {

            $table->after('id', function ($table) {
                $table->unsignedBigInteger('company_id')->nullable(); // Foreign key column
            });
            $table->foreign('company_id')
                ->references('id')
                ->on($companyTableName)
                ->cascadeOnUpdate()
                ->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table( unusualConfig('tables.users', 'admin_users'), function (Blueprint $table) {
            // $table->dropConstrainedForeignId(['company_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
