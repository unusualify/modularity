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
        $chatMessagesTable = modularityConfig('tables.chat_messages', 'um_chat_messages');
        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $chatMessagesTable = modularityConfig('tables.chat_messages', 'um_chat_messages');
        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->text('content')->nullable(false)->change();
        });
    }
};
