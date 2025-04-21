<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Assignment;
use Unusualify\Modularity\Entities\Enums\AssignmentStatus;

trait AssignableScopes
{
    public function getUserForAssignable($user = null)
    {
        if (! Auth::check() && ! $user) {
            return null;
        }

        $user = $user ?? Auth::user();

        if (! $user) {
            return null;
        }

        return $user;
    }

    /**
     * Scope to check if the current user is the assignee
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsActiveAssignee($query, $user = null)
    {
        if (! ($user = $this->getUserForAssignable($user))) {
            return $query;
        }

        $assignmentTable = (new Assignment())->getTable();
        $modelTable = $this->getTable();
        $modelClass = get_class($this);
        $userClass = get_class($user);

        return $query->whereExists(function ($subQuery) use ($assignmentTable, $modelTable, $modelClass, $user, $userClass) {
            // Create a SQL string for the subquery
            $latestAssignmentSql = \DB::table($assignmentTable)
                ->select(\DB::raw('MAX(created_at)'))
                ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                ->where("{$assignmentTable}.assignable_type", $modelClass)
                ->toSql();

            $subQuery->select(\DB::raw(1))
                ->from($assignmentTable)
                ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                ->where("{$assignmentTable}.assignable_type", $modelClass)
                ->where("{$assignmentTable}.assignee_id", $user->id)
                ->where("{$assignmentTable}.assignee_type", $userClass)
                ->whereRaw("{$assignmentTable}.created_at = ({$latestAssignmentSql})", [$modelClass]);
        });
    }

    public function scopeIsActiveAssigneeForYourRole($query, $user = null)
    {
        if (! ($user = $this->getUserForAssignable($user))) {
            return $query;
        }

        // Ensure the user model uses roles and retrieve role IDs
        if (!method_exists($user, 'roles')) {
            return $query->whereRaw('1 = 0'); // Return no results if roles aren't available
        }

        $userRoles = $user->roles;
        $userRoleIds = $userRoles->pluck('id')->toArray();
        $userClass = get_class($user);

        if (empty($userRoleIds)) {
            // User has no roles, so cannot match any assignments by role
            return $query->whereRaw('1 = 0'); // Return no results
        }

        $assignmentTable = (new Assignment())->getTable();
        $modelTable = $this->getTable();
        $modelClass = get_class($this);
        $userClass = get_class($user);

        $users = $userClass::whereHas('roles', function ($query) use ($userRoleIds) {
            $query->whereIn('id', $userRoleIds);
        })->get();

        // if user is exceptep from its role, filter it
        // $userIds = $users->filter(fn ($u) => $u->id !== $user->id)->pluck('id');
        // if ($userIds->isEmpty()) {
        //     return $query->whereRaw('1 = 0');
        // }
        $userIds = $users->pluck('id');

        $query->whereExists(function ($subQuery) use ($assignmentTable, $modelTable, $modelClass, $user, $userClass, $userIds) {
            $userIds->each(function ($userId) use ($subQuery, $assignmentTable, $modelTable, $modelClass, $user, $userClass) {
                $subQuery = $subQuery->orWhereExists(function ($subQuery) use ($assignmentTable, $modelTable, $modelClass, $user, $userClass, $userId) {
                    // Create a SQL string for the subquery
                    $latestAssignmentSql = \DB::table($assignmentTable)
                        ->select(\DB::raw('MAX(created_at)'))
                        ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                        ->where("{$assignmentTable}.assignable_type", $modelClass)
                        ->toSql();

                    $subQuery->select(\DB::raw(1))
                        ->from($assignmentTable)
                        ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                        ->where("{$assignmentTable}.assignable_type", $modelClass)
                        ->where("{$assignmentTable}.assignee_id", $userId)
                        ->where("{$assignmentTable}.assignee_type", $userClass)
                        ->whereRaw("{$assignmentTable}.created_at = ({$latestAssignmentSql})", [$modelClass]);
                });
            });
            return $subQuery;
        });

        return $query;
    }

    public function scopeLastStatusAssignment($query, $status)
    {
        $assignmentTable = (new Assignment())->getTable();
        $modelTable = $this->getTable();
        $modelClass = get_class($this);

        return $query->whereExists(function ($subQuery) use ($assignmentTable, $modelTable, $modelClass, $status) {
            // Create a SQL string for the subquery
            $latestAssignmentSql = \DB::table($assignmentTable)
                ->select(\DB::raw('MAX(created_at)'))
                ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                ->where("{$assignmentTable}.assignable_type", $modelClass)
                ->toSql();

            $subQuery->select(\DB::raw(1))
                ->from($assignmentTable)
                ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                ->where("{$assignmentTable}.assignable_type", $modelClass)
                ->where("{$assignmentTable}.status", $status)
                ->whereRaw("{$assignmentTable}.created_at = ({$latestAssignmentSql})", [$modelClass]);
        });
    }

    public function scopeCompletedAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::COMPLETED);
    }

    public function scopeYourCompletedAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::COMPLETED)->isActiveAssignee();
    }

    public function scopeTeamCompletedAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::COMPLETED)->isActiveAssignee();
    }

    public function scopePendingAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::PENDING);
    }

    public function scopeYourPendingAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::PENDING)->isActiveAssignee();
    }

    public function scopeTeamPendingAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::PENDING);
    }

    public function scopeCancelledAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::CANCELLED);
    }


    /**
     * Check if the current user has ever been assigned to the model
     *
     * @return bool
     */
    public function scopeEverAssignedToYou($query)
    {
        if (! ($user = $this->getUserForAssignable())) {
            return $query;
        }

        return $query->whereHas('assignments', function ($query) use ($user) {
            $query->isAssignee($user);
        });
    }

    /**
     * Scope to find models that have ever been assigned to users with the same roles as the current user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEverAssignedToYourRole($query)
    {
        if (! ($user = $this->getUserForAssignable())) {
            return $query;
        }

        $userModel = get_class($user);
        $userRoles = $user->roles;

        // Find models that have been assigned to any user with the same role
        return $query->whereHas('assignments', function ($assignmentQuery) use ($userModel, $userRoles) {
            $assignmentQuery->isAssigneeType($userModel)
                ->isAssigneeRole($userRoles);
        });
    }

    /**
     * Scope to check if the current user has ever been assigned to the model or has authorization
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeEverAssignedToYouOrHasAuthorization($query)
    {
        if (! ($user = $this->getUserForAssignable())) {
            return $query;
        }

        if (in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($user))) {
            // Get roles to check from model's static property if defined
            $rolesToCheck = static::$assignableRolesToCheck ?? null;

            // If no specific roles defined, get all roles from the user
            if (! (is_null($rolesToCheck) || empty($rolesToCheck)) ) {
                // Check for specific roles
                $roleModel = config('permission.models.role');
                $existingRoles = $roleModel::whereIn('name', $rolesToCheck)->get();

                if (!$user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                    return $query;
                }
            }
        }

        return $query->everAssignedToYou($user);
    }

    /**
     * Scope to check if the current user has ever been assigned to the model or has authorization
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeEverAssignedToYourRoleOrHasAuthorization($query)
    {
        if (! ($user = $this->getUserForAssignable())) {
            return $query;
        }

        if (in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($user))) {
            // Get roles to check from model's static property if defined
            $rolesToCheck = static::$assignableRolesToCheck ?? null;

            // If no specific roles defined, get all roles from the user
            if (! (is_null($rolesToCheck) || empty($rolesToCheck)) ) {
                // Check for specific roles
                $roleModel = config('permission.models.role');
                $existingRoles = $roleModel::whereIn('name', $rolesToCheck)->get();

                if (!$user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                    return $query;
                }
            }
        }

        return $query->everAssignedToYourRole($user);
    }
}