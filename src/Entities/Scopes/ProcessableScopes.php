<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ProcessableScopes
{
    public function scopeIsConfirmedProcess(Builder $query)
    {
        return $query->whereHas('process', function (Builder $query) {
            $query->isConfirmed();
        });
    }

    public function scopeIsWaitingForConfirmationProcess(Builder $query)
    {
        return $query->whereHas('process', function (Builder $query) {
            $query->isWaitingForConfirmation();
        });
    }

    public function scopeIsWaitingForReactionProcess(Builder $query)
    {
        return $query->whereHas('process', function (Builder $query) {
            $query->isWaitingForReaction();
        });
    }

    public function scopeIsRejectedProcess(Builder $query)
    {
        return $query->whereHas('process', function (Builder $query) {
            $query->isRejected();
        });
    }

    public function scopeIsPreparingProcess(Builder $query)
    {
        return $query->whereHas('process', function (Builder $query) {
            $query->isPreparing();
        });
    }
}
