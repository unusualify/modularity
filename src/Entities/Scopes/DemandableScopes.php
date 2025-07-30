<?php

namespace Unusualify\Modularity\Entities\Scopes;

trait DemandableScopes
{
    public function scopeByDemandStatus($query, $status)
    {
        return $query->whereHas('demands', function ($query) use ($status) {
            $query->byStatus($status);
        });
    }

    public function scopeByDemandPriority($query, $priority)
    {
        return $query->whereHas('demands', function ($query) use ($priority) {
            $query->byPriority($priority);
        });
    }

    public function scopeIsDemandPending($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->isPending();
        });
    }

    public function scopeIsDemandEvaluated($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->isEvaluated();
        });
    }

    public function scopeIsDemandInReview($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->inReview();
        });
    }

    public function scopeIsDemandAnswered($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->isAnswered();
        });
    }

    public function scopeIsDemandRejected($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->isRejected();
        });
    }

    public function scopeIsDemandActive($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->isActive();
        });
    }

    public function scopeIsDemandResolved($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->isResolved();
        });
    }

    public function scopeIsDemandOverdue($query)
    {
        return $query->whereHas('demands', function ($query) {
            $query->overdue();
        });
    }

    public function scopeByDemandPriorityOrder($query, $direction = 'desc')
    {
        return $query->whereHas('demands', function ($query) use ($direction) {
            $query->byPriorityOrder($direction);
        });
    }

    public function scopeByDemandDueAt($query, $direction = 'desc')
    {
        return $query->whereHas('demands', function ($query) use ($direction) {
            $query->byDueAt($direction);
        });
    }
}
