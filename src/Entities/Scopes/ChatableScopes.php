<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Unusualify\Modularity\Entities\ChatMessage;

trait ChatableScopes
{
    public function scopeHasChatMessages(Builder $query): Builder
    {
        return $query->whereHas('chatMessages');
    }

    public function scopeHasUnreadChatMessages(Builder $query): Builder
    {
        return $query->whereHas('chatMessages', function (Builder $query) {
            $query->unread();
        });
    }

    public function scopeHasUnreadChatMessagesForYou(Builder $query, $guardName = null): Builder
    {
        return $query->whereHas('chatMessages', function (Builder $query) use ($guardName) {
            $query->where('is_read', false)->whereNot(fn ($query) => $query->authorized($guardName));
        });
    }

    public function scopeHasUnansweredChatMessageFromClient(Builder $query): Builder
    {
        return $query->whereHas('latestChatMessage', function (Builder $query) {
            $query->fromClient();
        });
    }

    public function scopeHasUnansweredChatMessageFromCreator(Builder $query): Builder
    {
        $creatorRecordTable = modularityConfig('tables.creator_records', 'um_creator_records');
        $chatMessageTable = (new ChatMessage)->getTable();

        return $query->whereHas('latestChatMessage', function ($messageQuery) use ($creatorRecordTable, $chatMessageTable) {
            $messageQuery->whereExists(function ($subQuery) use ($creatorRecordTable, $chatMessageTable) {
                $creatableTableAlias = 'creatable_creators';
                $chatableTableAlias = 'chatable_creators';

                $subQuery->select(\DB::raw(1))
                    ->from($creatorRecordTable . ' as ' . $creatableTableAlias)
                    ->join($creatorRecordTable . ' as ' . $chatableTableAlias, function ($join) use ($creatableTableAlias, $chatableTableAlias) {
                        $join->on($creatableTableAlias . '.creator_id', '=', $chatableTableAlias . '.creator_id')
                            ->on($creatableTableAlias . '.guard_name', '=', $chatableTableAlias . '.guard_name');
                    })
                    ->whereColumn($creatableTableAlias . '.creatable_id', $this->getTable() . '.id')
                    ->where($creatableTableAlias . '.creatable_type', static::class)
                    ->whereColumn($chatableTableAlias . '.creatable_id', $chatMessageTable . '.id')
                    ->where($chatableTableAlias . '.creatable_type', ChatMessage::class);
            });
        });
    }

    /**
     * Scope to get models that have a notifiable message.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $minuteOffset
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasNotifiableMessage(Builder $query, $minuteOffset = null): Builder
    {
        $chatMessageTable = (new ChatMessage)->getTable();
        return $query->whereHas('latestChatMessage', function (Builder $query) use ($minuteOffset, $chatMessageTable) {

            $query->where('is_read', false)->whereNull('notified_at')->when($minuteOffset, function ($query) use ($minuteOffset, $chatMessageTable) {
                $query->where($chatMessageTable . '.created_at', '<', now()->subMinutes($minuteOffset));
            });
        });
    }
}
