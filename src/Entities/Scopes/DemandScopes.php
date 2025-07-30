<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Unusualify\Modularity\Entities\Enums\DemandStatus;
use Unusualify\Modularity\Entities\Enums\DemandPriority;

trait DemandScopes
{
    public function scopeIsDemander($query, $user)
    {
        return $query->where('demander_id', $user->id)
            ->where('demander_type', get_class($user));
    }

    public function scopeIsResponder($query, $user)
    {
        return $query->where('responder_id', $user->id)
            ->where('responder_type', get_class($user));
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeIsPending($query)
    {
        return $query->where('status', DemandStatus::PENDING);
    }

    public function scopeIsEvaluated($query)
    {
        return $query->where('status', DemandStatus::EVALUATED);
    }

    public function scopeInReview($query)
    {
        return $query->where('status', DemandStatus::IN_REVIEW);
    }

    public function scopeIsAnswered($query)
    {
        return $query->where('status', DemandStatus::ANSWERED);
    }

    public function scopeIsRejected($query)
    {
        return $query->where('status', DemandStatus::REJECTED);
    }

    public function scopeIsActive($query)
    {
        return $query->whereIn('status', [
            DemandStatus::PENDING,
            DemandStatus::EVALUATED,
            DemandStatus::IN_REVIEW
        ]);
    }

    public function scopeIsResolved($query)
    {
        return $query->whereIn('status', [
            DemandStatus::ANSWERED,
            DemandStatus::REJECTED
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
            ->whereIn('status', [
                DemandStatus::PENDING,
                DemandStatus::EVALUATED,
                DemandStatus::IN_REVIEW
            ]);
    }

    public function scopeByPriorityOrder($query, $direction = 'desc')
    {
        $priorityOrder = [
            DemandPriority::URGENT->value => 4,
            DemandPriority::HIGH->value => 3,
            DemandPriority::MEDIUM->value => 2,
            DemandPriority::LOW->value => 1,
        ];

        return $query->orderByRaw(
            "CASE priority " .
            implode(' ', array_map(
                fn($priority, $order) => "WHEN '{$priority}' THEN {$order}",
                array_keys($priorityOrder),
                array_values($priorityOrder)
            )) .
            " END {$direction}"
        );
    }

    public function scopeRootDemands($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithThread($query)
    {
        return $query->with(['children' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);
    }
}
