<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Facades\MigrationBackup;

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
                $table->boolean('is_read')->default(false);
                $table->boolean('is_starred')->default(false);
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_sent')->default(false);
                $table->boolean('is_received')->default(false);

                $table->timestamp('edited_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        MigrationBackup::restore();
    }

    public function down()
    {
        $modularityChatsTable = modularityConfig('tables.chats', 'modularity_chats');
        $modularityChatMessagesTable = modularityConfig('tables.chat_messages', 'modularity_chat_messages');

        MigrationBackup::backup($modularityChatMessagesTable);
        MigrationBackup::backup($modularityChatsTable);

        Schema::dropIfExists($modularityChatMessagesTable);
        Schema::dropIfExists($modularityChatsTable);
    }
};
