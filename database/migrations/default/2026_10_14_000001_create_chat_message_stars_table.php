<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\ChatMessage;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stars become private to each user: they move from the shared is_starred
     * column to a per-user table. A message that was starred before stays starred
     * for every participant who has sent a message in its chat, so nobody loses a star.
     */
    public function up(): void
    {
        $chatMessagesTable = modularousConfig('tables.chat_messages', 'um_chat_messages');
        $chatMessageStarsTable = modularousConfig('tables.chat_message_stars', 'um_chat_message_stars');
        $creatorRecordsTable = modularousConfig('tables.creator_records', 'um_creator_records');

        if (! Schema::hasTable($chatMessageStarsTable)) {
            Schema::create($chatMessageStarsTable, function (Blueprint $table) use ($chatMessagesTable) {
                $table->id();
                $table->foreignId('chat_message_id')
                    ->constrained(table: $chatMessagesTable, indexName: 'fk_chat_message_stars_chat_message_id')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->morphs('user');
                $table->timestamps();

                $table->unique(['chat_message_id', 'user_type', 'user_id'], 'chat_message_stars_unique');
            });
        }

        if (! Schema::hasColumn($chatMessagesTable, 'is_starred')) {
            return;
        }

        if (Schema::hasTable($creatorRecordsTable)) {
            $now = now();

            DB::table("{$chatMessagesTable} as starred")
                ->join("{$chatMessagesTable} as sent", 'sent.chat_id', '=', 'starred.chat_id')
                ->join("{$creatorRecordsTable} as records", function ($join) {
                    $join->on('records.creatable_id', '=', 'sent.id')
                        ->where('records.creatable_type', (new ChatMessage)->getMorphClass());
                })
                ->where('starred.is_starred', true)
                ->select('starred.id as chat_message_id', 'records.creator_type as user_type', 'records.creator_id as user_id')
                ->distinct()
                ->orderBy('starred.id')
                ->chunk(500, function ($stars) use ($chatMessageStarsTable, $now) {
                    DB::table($chatMessageStarsTable)->insertOrIgnore($stars->map(fn ($star) => [
                        'chat_message_id' => $star->chat_message_id,
                        'user_type' => $star->user_type,
                        'user_id' => $star->user_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all());
                });
        }

        Schema::table($chatMessagesTable, function (Blueprint $table) {
            $table->dropColumn('is_starred');
        });
    }

    /**
     * Reverse the migrations.
     *
     * A message starred by anyone becomes starred for everyone again.
     */
    public function down(): void
    {
        $chatMessagesTable = modularousConfig('tables.chat_messages', 'um_chat_messages');
        $chatMessageStarsTable = modularousConfig('tables.chat_message_stars', 'um_chat_message_stars');

        if (! Schema::hasColumn($chatMessagesTable, 'is_starred')) {
            Schema::table($chatMessagesTable, function (Blueprint $table) {
                $table->boolean('is_starred')->default(false)->after('is_read');
            });
        }

        if (Schema::hasTable($chatMessageStarsTable)) {
            DB::table($chatMessagesTable)
                ->whereIn('id', DB::table($chatMessageStarsTable)->select('chat_message_id'))
                ->update(['is_starred' => true]);
        }

        Schema::dropIfExists($chatMessageStarsTable);
    }
};
