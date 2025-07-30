<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Unusualify\Modularity\Entities\Demand;
use Unusualify\Modularity\Entities\Scopes\DemandableScopes;

trait Demandable
{
    use DemandableScopes;

    /**
     * Perform any actions when booting the trait
     */
    public static function bootDemandable(): void
    {
        static::retrieved(function (Model $model) {});
    }

    /**
     * Laravel hook to initialize the trait
     */
    public function initializeDemandable(): void
    {
        $this->append([
            'active_demand_status',
            'demands_count',
            'pending_demands_count',
            'resolved_demands_count',
            'last_demand_priority',
            'has_urgent_demands',
        ]);
    }

    /**
     * Get all demands for the model
     */
    public function demands(): MorphMany
    {
        return $this->morphMany(Demand::class, 'demandable');
    }

    /**
     * Get the last demand for the model
     */
    public function lastDemand(): MorphOne
    {
        return $this->morphOne(Demand::class, 'demandable')
            ->latestOfMany('created_at');
    }

    /**
     * Get active demands (pending, evaluated, in_review)
     */
    public function activeDemands(): MorphMany
    {
        return $this->morphMany(Demand::class, 'demandable')
            ->isActive();
    }

    /**
     * Get pending demands
     */
    public function pendingDemands(): MorphMany
    {
        return $this->morphMany(Demand::class, 'demandable')
            ->isPending();
    }

    /**
     * Get resolved demands (answered, rejected)
     */
    public function resolvedDemands(): MorphMany
    {
        return $this->morphMany(Demand::class, 'demandable')
            ->isResolved();
    }

    /**
     * Get urgent demands
     */
    public function urgentDemands(): MorphMany
    {
        return $this->morphMany(Demand::class, 'demandable')
            ->byPriority('urgent')
            ->isActive();
    }

    public function createDemand($data)
    {
        return $this->demands()->create($data);
    }

    public function createDemands($data)
    {
        return $this->demands()->createMany($data);
    }

    // Accessors
    protected function activeDemandStatus(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->lastDemand && $this->lastDemand->status->isActive()
                ? "<v-chip variant='text' color='{$this->lastDemand->status->iconColor()}' prepend-icon='{$this->lastDemand->status->icon()}'>{$this->lastDemand->status->label()}</v-chip>"
                : null
        );
    }

    protected function demandsCount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->demands()->count(),
        );
    }

    protected function pendingDemandsCount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->pendingDemands()->count(),
        );
    }

    protected function resolvedDemandsCount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->resolvedDemands()->count(),
        );
    }

    protected function lastDemandPriority(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->lastDemand
                ? "<v-chip size='small' variant='text' color='{$this->lastDemand->priority->iconColor()}' prepend-icon='{$this->lastDemand->priority->icon()}'>{$this->lastDemand->priority->label()}</v-chip>"
                : null
        );
    }

    protected function hasUrgentDemands(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->urgentDemands()->exists(),
        );
    }
}
