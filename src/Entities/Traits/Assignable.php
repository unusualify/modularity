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

    public function assignments() : MorphMany
    {
        return $this->morphMany(Assignment::class, 'assignable');
    }

    public function lastAssignment() : MorphOne
    {
        return $this->morphOne(Assignment::class, 'assignable')
            ->latest();
    }

    protected function activeAssigneeName() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->lastAssignment ? $this->lastAssignment->assignee->name : null,
        );
    }

    public function scopeIsAssignee($query)
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

        return $query->whereHas('lastAssignment', function ($query) use ($user) {
            $query->where('assignee_id', $user->id)
                ->where('assignee_type', get_class($user));
        });
    }

}
