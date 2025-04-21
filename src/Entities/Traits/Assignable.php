<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Assignment;

trait Assignable
{

    /**
     * Perform any actions when booting the trait
     *
     * @return void
     */
    public static function bootAssignable(): void
    {
        static::retrieved(function (Model $model) {});
    }

    /**
     * Laravel hook to initialize the trait
     *
     * @return void
     */
    public function initializeAssignable(): void
    {
        $this->append('active_assignee_name');
    }

    /**
     * Get all assignments for the model
     *
     * @return MorphMany
     */
    public function assignments() : MorphMany
    {
        return $this->morphMany(Assignment::class, 'assignable');
    }

    /**
     * Get the last assignment for the model
     *
     * @return MorphOne
     */
    public function lastAssignment() : MorphOne
    {
        return $this->morphOne(Assignment::class, 'assignable')
            ->latest('created_at');
    }

    protected function activeAssigneeName() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->lastAssignment ? $this->lastAssignment->assignee->name : null,
        );
    }

    /**
     * Scope to check if the current user is the assignee
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsActiveAssignee($query)
    {
        if (! Auth::check()) {
            return $query;
        }

        $user = Auth::user();

        if (! $user) {
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

    /**
     * Check if the current user has ever been assigned to the model
     *
     * @return bool
     */
    public function scopeEverAssignedToYou($query)
    {
        if (! Auth::check()) {
            return $query;
        }

        $user = Auth::user();

        if (! $user) {
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
        if (! Auth::check()) {
            return $query;
        }

        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        $userModel = get_class($user);
        $userRoles = $user->roles;

        // Find models that have been assigned to any user with the same role
        return $query->whereHas('assignments', function ($assignmentQuery) use ($userModel, $userRoles) {
            $assignmentQuery->isAssigneeType($userModel)
                ->isAssigneeRole($userRoles);
                // ->whereHas('assignee', function ($userQuery) use ($userRoles) {
                //     $userQuery->role($userRoles);
                // });
        });
    }

    public function scopeCompletedAssignments($query)
    {
        return $query->whereHas('assignments', function ($query) {
            $query->isCompleted();
        });
    }

    public function scopePendingAssignments($query)
    {
        return $query->whereHas('assignments', function ($query) {
            $query->isPending();
        });
    }

    public function scopeCancelledAssignments($query)
    {
        return $query->whereHas('assignments', function ($query) {
            $query->isCancelled();
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
        if (! Auth::check()) {
            return $query;
        }

        $user = $user ?? Auth::user();

        if (! $user) {
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
        if (! Auth::check()) {
            return $query;
        }

        $user = $user ?? Auth::user();

        if (! $user) {
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
