<?php

namespace Unusualify\Modularous\Entities\Scopes;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularous\Entities\Traits\HasCreator;

trait ChatMessageScopes
{
    use HasCreator;

    /**
     * Pinned messages whose pin has not expired yet (a null pinned_until never expires).
     */
    public function scopeActivelyPinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true)
            ->where(fn (Builder $query) => $query->whereNull('pinned_until')->orWhere('pinned_until', '>', now()));
    }

    /**
     * Pinned messages whose pin duration has ended.
     */
    public function scopeExpiredPins(Builder $query): Builder
    {
        return $query->where('is_pinned', true)
            ->whereNotNull('pinned_until')
            ->where('pinned_until', '<=', now());
    }

    /**
     * Messages starred by the given user (the current user by default).
     */
    public function scopeStarredBy(Builder $query, ?Authenticatable $user = null): Builder
    {
        $user ??= Auth::user();

        if (! $user instanceof \Illuminate\Database\Eloquent\Model) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('stars', fn (Builder $query) => $query
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey()));
    }

    /**
     * Load whether the given user (the current user by default) starred each message
     * into is_starred, so it is not looked up message by message.
     */
    public function scopeWithStarredFor(Builder $query, ?Authenticatable $user = null): Builder
    {
        $user ??= Auth::user();

        if (! $user instanceof \Illuminate\Database\Eloquent\Model) {
            return $query;
        }

        return $query->withExists(['stars as is_starred' => fn (Builder $query) => $query
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey())]);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeUnreadForYou(Builder $query, $guardName = null): Builder
    {
        return $query->where('is_read', false)->whereNot(fn ($query) => $query->hasAccessToCreation(null, $guardName));
    }

    public function scopeFromClient(Builder $query): Builder
    {
        // Avoid the HasCreator global `creator_record_exists` (withExists) scope here:
        // it adds a nested EXISTS on um_creator_records to every ChatMessage subquery and
        // makes parent counts (e.g. whereHas('latestChatMessage', fromClient)) extremely slow.
        // return $query->withoutGlobalScope('creator_record_exists')
        //     ->whereHas('creator', function (Builder $query) {
        //         $query->role(['client-manager', 'client-assistant']);
        //     });

        return $query->whereHas('creator', function (Builder $query) {
            $query->role(['client-manager', 'client-assistant']);
        });
    }

    public function scopeFromCreator(Builder $query, $creatorId, $creatorType): Builder
    {
        return $query->whereHas('creator', function (Builder $query) use ($creatorId, $creatorType) {
            $query->where('creator_id', $creatorId)->where('creator_type', $creatorType);
        });
    }
}
