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

        $assignmentTable = (new Assignment)->getTable();
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

    /**
     * Scope to check if the current user is the assignee for their role
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsActiveAssigneeForYourRole($query, $user = null)
    {
        if (! ($user = $this->getUserForAssignable($user))) {
            return $query;
        }

        // Ensure the user model uses roles and retrieve role IDs
        if (! method_exists($user, 'roles')) {
            return $query->whereRaw('1 = 0'); // Return no results if roles aren't available
        }

        $userRoles = $user->roles;
        $userRoleIds = $userRoles->pluck('id')->toArray();
        $userClass = get_class($user);

        if (empty($userRoleIds)) {
            // User has no roles, so cannot match any assignments by role
            return $query->whereRaw('1 = 0'); // Return no results
        }

        $assignmentTable = (new Assignment)->getTable();
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

        $query->whereExists(function ($subQuery) use ($assignmentTable, $modelTable, $modelClass, $userClass, $userIds) {
            $userIds->each(function ($userId) use ($subQuery, $assignmentTable, $modelTable, $modelClass, $userClass) {
                $subQuery = $subQuery->orWhereExists(function ($subQuery) use ($assignmentTable, $modelTable, $modelClass, $userClass, $userId) {
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

    /**
     * Scope to get the last status assignment
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLastStatusAssignment($query, $status, $dateColumn = null, $dateRange = null)
    {
        $assignmentTable = (new Assignment)->getTable();
        $modelTable = $this->getTable();
        $modelClass = get_class($this);

        $userTimezone = session('timezone') ?? 'Europe/London';

        return $query->whereExists(function ($subQuery) use ($assignmentTable, $modelTable, $modelClass, $status, $dateColumn, $dateRange, $userTimezone) {
            // Create a SQL string for the subquery
            $latestAssignmentSql = \DB::table($assignmentTable)
                ->select(\DB::raw('MAX(created_at)'))
                ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                ->where("{$assignmentTable}.assignable_type", $modelClass)
                ->toSql();

            if ($dateColumn && $dateRange) {

                if (is_array($dateRange) && count($dateRange) > 0) {
                    if (count($dateRange) == 1) {
                        $startDate = array_shift($dateRange);
                        $latestAssignmentSql .= " AND {$assignmentTable}.{$dateColumn} >= '{$startDate}'";
                    } elseif (count($dateRange) == 2) {
                        $startDate = array_shift($dateRange);
                        $endDate = array_pop($dateRange);
                        $latestAssignmentSql .= " AND {$assignmentTable}.{$dateColumn} BETWEEN '{$startDate}' AND '{$endDate}'";
                    }
                } elseif (is_string($dateRange) && $dateRange == 'today') {
                    // Get user's local start and end of day, then convert to UTC for database comparison
                    $startDate = now($userTimezone)->startOfDay()->utc()->format('Y-m-d H:i:s');
                    $endDate = now($userTimezone)->endOfDay()->utc()->format('Y-m-d H:i:s');

                    $latestAssignmentSql .= " AND {$assignmentTable}.{$dateColumn} BETWEEN '{$startDate}' AND '{$endDate}'";
                } elseif (is_string($dateRange) && $dateRange == 'this_week') {
                    $startDate = now($userTimezone)->startOfWeek()->utc()->format('Y-m-d H:i:s');
                    $endDate = now($userTimezone)->endOfWeek()->utc()->format('Y-m-d H:i:s');
                    $latestAssignmentSql .= " AND {$assignmentTable}.{$dateColumn} BETWEEN '{$startDate}' AND '{$endDate}'";
                } elseif (is_string($dateRange) && $dateRange == 'this_month') {
                    $startDate = now($userTimezone)->startOfMonth()->utc()->format('Y-m-d H:i:s');
                    $endDate = now($userTimezone)->endOfMonth()->utc()->format('Y-m-d H:i:s');
                    $latestAssignmentSql .= " AND {$assignmentTable}.{$dateColumn} BETWEEN '{$startDate}' AND '{$endDate}'";
                }
            }

            $subQuery->select(\DB::raw(1))
                ->from($assignmentTable)
                ->whereColumn("{$assignmentTable}.assignable_id", "{$modelTable}.id")
                ->where("{$assignmentTable}.assignable_type", $modelClass)
                ->where("{$assignmentTable}.status", $status)
                ->whereRaw("{$assignmentTable}.created_at = ({$latestAssignmentSql})", [$modelClass]);
        });
    }

    /**
     * Scope to get the completed assignments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompletedAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::COMPLETED);
    }

    /**
     * Scope to get the completed assignments for the current user
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeYourCompletedAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::COMPLETED)->isActiveAssignee();
    }

    /**
     * Scope to get the completed assignments for the current user's role
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTeamCompletedAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::COMPLETED)->isActiveAssigneeForYourRole();
    }

    /**
     * Scope to get the pending assignments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePendingAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::PENDING);
    }

    /**
     * Scope to get the pending assignments for the current user
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeYourPendingAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::PENDING)->isActiveAssignee();
    }

    /**
     * Scope to get the pending assignments for the current user's role
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTeamPendingAssignments($query)
    {
        return $query->lastStatusAssignment(AssignmentStatus::PENDING)->isActiveAssigneeForYourRole();
    }

    /**
     * Scope to get the cancelled assignments
     *
     * @param Builder $query
     * @return Builder
     */
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
            if (! (is_null($rolesToCheck) || empty($rolesToCheck))) {
                // Check for specific roles
                $roleModel = config('permission.models.role');
                $existingRoles = $roleModel::whereIn('name', $rolesToCheck)->get();

                if (! $user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
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
            if (! (is_null($rolesToCheck) || empty($rolesToCheck))) {
                // Check for specific roles
                $roleModel = config('permission.models.role');
                $existingRoles = $roleModel::whereIn('name', $rolesToCheck)->get();

                if (! $user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                    return $query;
                }
            }
        }

        return $query->everAssignedToYourRole($user);
    }
}
