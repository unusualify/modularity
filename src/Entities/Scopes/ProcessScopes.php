<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;

trait ProcessScopes
{
    public function scopeStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeIsConfirmed(Builder $query)
    {
        return $query->status(ProcessStatus::CONFIRMED);
    }

    public function scopeIsWaitingForConfirmation(Builder $query)
    {
        return $query->status(ProcessStatus::WAITING_FOR_CONFIRMATION);
    }

    public function scopeIsWaitingForReaction(Builder $query)
    {
        return $query->status(ProcessStatus::WAITING_FOR_REACTION);
    }

    public function scopeIsRejected(Builder $query)
    {
        return $query->status(ProcessStatus::REJECTED);
    }

    public function scopeIsPreparing(Builder $query)
    {
        return $query->status(ProcessStatus::PREPARING);
    }
}
