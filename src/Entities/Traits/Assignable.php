<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Unusualify\Modularity\Entities\Assignment;
use Unusualify\Modularity\Entities\Scopes\AssignableScopes;

trait Assignable
{
    use AssignableScopes;

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
}
