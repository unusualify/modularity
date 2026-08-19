<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = modularousConfig('tables.module_route_statuses', 'um_module_route_statuses');

        if (! Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table): void {
                $table->{modularousIncrementsMethod()}('id');
                $table->string('module');
                $table->string('route');
                $table->boolean('enabled')->default(true);
                $table->timestamps();

                $table->unique(['module', 'route'], 'um_module_route_statuses_module_route_unique');
                $table->index('module');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(modularousConfig('tables.module_route_statuses', 'um_module_route_statuses'));
    }
};
