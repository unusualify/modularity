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
            $table->timestamp('notified_at')->nullable()->after('edited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $chatMessagesTable = modularityConfig('tables.chat_messages', 'um_chat_messages');
        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
    }
};
