<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;

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

    public function scopeHasChatMessageWaitingReaction(Builder $query): Builder
    {
        return $query->whereHas('latestChatMessage', function (Builder $query) {
            $query->fromClient();
        });
    }
}
