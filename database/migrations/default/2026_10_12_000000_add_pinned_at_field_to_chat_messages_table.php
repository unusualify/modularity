<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $chatMessagesTable = modularousConfig('tables.chat_messages', 'um_chat_messages');

        if (Schema::hasColumn($chatMessagesTable, 'pinned_at')) {
            return;
        }

        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->timestamp('pinned_at')->nullable()->after('notified_at');
        });

        // already pinned messages: the last update is the best guess for the pin time
        DB::table($chatMessagesTable)
            ->where('is_pinned', true)
            ->update(['pinned_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $chatMessagesTable = modularousConfig('tables.chat_messages', 'um_chat_messages');

        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->dropColumn('pinned_at');
        });
    }
};
