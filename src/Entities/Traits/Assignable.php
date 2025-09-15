<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Unusualify\Modularity\Entities\Assignment;
use Unusualify\Modularity\Entities\Scopes\AssignableScopes;


trait Assignable
{
    use AssignableScopes;

    /**
     * Perform any actions when booting the trait
     */
    public static function bootAssignable(): void
    {
        static::deleting(function (Model $model) {
            $assignmentsRelation = $model->assignments();
            $assignmentModel = $assignmentsRelation->getRelated();

            // soft delete
            if(class_uses_recursive(static::class) && in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
                $assignmentsRelation->delete();
            } else {
                $assignmentsRelation->forceDelete();
            }
        });

        if(class_uses_recursive(static::class) && in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::forceDeleting(function (Model $model) {
                $assignmentsRelation = $model->assignments();
                $assignmentsRelation->forceDelete();
            });
        };
    }

    /**
     * Laravel hook to initialize the trait
     */
    public function initializeAssignable(): void
    {
        $this->append('active_assignee_name');
    }

    /**
     * Get all assignments for the model
     */
    public function assignments(): MorphMany
    {
        return $this->morphMany(Assignment::class, 'assignable');
    }

    /**
     * Get the last assignment for the model
     */
    public function lastAssignment(): MorphOne
    {
        return $this->morphOne(Assignment::class, 'assignable')
            ->latest('created_at');
    }

    protected function activeAssigneeName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->lastAssignment ? $this->lastAssignment->assignee->name : null,
        );

    }

    protected function activeAssignerName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->lastAssignment ? $this->lastAssignment->assigner->name : null,
        );
    }

    protected function activeAssignmentStatus(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->lastAssignment
                ? "<v-chip variant='text' color='{$this->lastAssignment->statusIconColor}' prepend-icon='{$this->lastAssignment->statusIcon}'>{$this->lastAssignment->statusLabel}</v-chip>"
                : null
        );
    }
}
