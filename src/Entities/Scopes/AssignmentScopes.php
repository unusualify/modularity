<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Unusualify\Modularity\Entities\Enums\AssignmentStatus;

trait AssignmentScopes
{
    public function scopeIsAssigneeType($query, $type)
    {
        return $query->where('assignee_type', $type);
    }

    public function scopeIsAssignee($query, $user)
    {
        return $query->where('assignee_id', $user->id)
            ->where('assignee_type', get_class($user));
    }

    public function scopeIsAssigneeRole($query, $roles)
    {
        return $query->whereHas('assignee', function ($query) use ($roles) {
            $query->role($roles);
        });
    }

    public function scopeIsCompleted($query)
    {
        return $query->where('status', AssignmentStatus::COMPLETED);
    }

    public function scopeIsPending($query)
    {
        return $query->where('status', AssignmentStatus::PENDING);
    }

    public function scopeIsCancelled($query)
    {
        return $query->where('status', AssignmentStatus::REJECTED);
    }
}