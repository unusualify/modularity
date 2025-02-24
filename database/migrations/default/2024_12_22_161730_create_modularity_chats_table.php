<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $modularityChatsTable = modularityConfig('tables.chats', 'modularity_chats');
        $modularityChatMessagesTable = modularityConfig('tables.chat_messages', 'modularity_chat_messages');

        if (! Schema::hasTable($modularityChatsTable)) {
            Schema::create($modularityChatsTable, function (Blueprint $table) {
                // this will create an id, name field
                createDefaultTableFields($table);
                $table->uuidMorphs('chatable');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable($modularityChatMessagesTable)) {
            Schema::create($modularityChatMessagesTable, function (Blueprint $table) use ($modularityChatsTable) {
                // this will create an id, name field
                createDefaultTableFields($table);
                $table->foreignId('chat_id')
                    ->constrained(table: $modularityChatsTable, indexName: 'fk_chat_messages_chat_id')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->text('content');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        $modularityChatsTable = modularityConfig('tables.chats', 'modularity_chats');
        $modularityChatMessagesTable = modularityConfig('tables.chat_messages', 'modularity_chat_messages');

        Schema::dropIfExists($modularityChatMessagesTable);
        Schema::dropIfExists($modularityChatsTable);
    }
};
