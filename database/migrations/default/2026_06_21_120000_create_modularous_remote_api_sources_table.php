<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = modularousConfig('tables.remote_api_sources', 'um_remote_api_sources');

        if (! Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table) {
                $table->{modularousIncrementsMethod()}('id');
                $table->morphs('sourceable');
                $table->unsignedBigInteger('remote_id')->nullable()->index();
                $table->json('synced_attributes')->nullable();
                $table->json('remote_payload')->nullable();
                $table->timestamp('remote_synced_at')->nullable();
                $table->timestamps();

                $table->unique(['sourceable_type', 'remote_id'], 'um_remote_api_sources_sourceable_remote_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(modularousConfig('tables.remote_api_sources', 'um_remote_api_sources'));
    }
};
