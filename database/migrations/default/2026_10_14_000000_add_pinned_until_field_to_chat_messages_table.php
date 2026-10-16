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
        $chatMessagesTable = modularousConfig('tables.chat_messages', 'um_chat_messages');

        if (Schema::hasColumn($chatMessagesTable, 'pinned_until')) {
            return;
        }

        // already pinned messages keep a null pinned_until, so they stay pinned until unpinned
        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->timestamp('pinned_until', 6)->nullable()->after('pinned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $chatMessagesTable = modularousConfig('tables.chat_messages', 'um_chat_messages');

        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->dropColumn('pinned_until');
        });
    }
};
