<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Unusualify\Modularity\Entities\Traits\HasCreator;

trait ChatMessageScopes
{
    use HasCreator;

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeUnreadForYou(Builder $query, $guardName = null): Builder
    {
        return $query->where('is_read', false)->whereNot(fn ($query) => $query->authorized($guardName));
    }

    public function scopeFromClient(Builder $query): Builder
    {
        return $query->whereHas('creator', function (Builder $query) {
            $query->role(['client-manager', 'client-assistant']);
        });
    }
}
