<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Facades\Modularity;

trait HasCreator
{
    /**
     * Indicates if a custom creator is currently being saved.
     *
     * @var bool
     */
    protected $isCustomCreatorSaving = false;

    /**
     * The fillable attributes for the creator record.
     *
     * @var array
     */
    protected static $hasCreatorFillable = ['custom_creator_id', 'custom_creator_type', 'custom_guard_name'];

    /**
     * Custom fields for the creator record.
     *
     * @var array
     */
    protected $customHasCreatorFields = [];

    protected $creatableClass;

    protected static function bootHasCreator()
    {
        static::created(function ($model) {
            if ($model->isCustomCreatorSaving) {
                $model->creatorRecord()->create($model->customHasCreatorFields);
                $model->isCustomCreatorSaving = false;
                $model->customHasCreatorFields = [];
            } elseif (Auth::check()) {
                $guard = Auth::guard();
                $model->creatorRecord()->create([
                    'creator_id' => $guard->id(), // creator user id
                    'creator_type' => $guard->getProvider()->getModel(), // creator model
                    'guard_name' => $guard->name,
                ]);
            }
        });

        static::saving(function ($model) {
            if ($model->custom_creator_id) {
                $guard = Auth::guard();
                $model->isCustomCreatorSaving = true;
                $model->customHasCreatorFields = [
                    'creator_id' => $model->custom_creator_id,
                    'creator_type' => $model->custom_creator_type ?? static::getDefaultCreatorModel(),
                    'guard_name' => $model->custom_guard_name ?? $guard->name,
                ];
            }

            foreach (static::$hasCreatorFillable as $field) {
                $model->offsetUnset($field);
            }
        });

        static::creating(function ($model) {
            // dd($model);
        });

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            // Add deleted event handler
            static::forceDeleting(function ($model) {
                // This will automatically delete the associated authorized record
                $model->creatorRecord()->delete();
            });
        } else {
            // Add deleted event handler
            static::deleting(function ($model) {
                // This will automatically delete the associated authorized record
                $model->creatorRecord()->delete();
            });
        }

    }

    public function initializeHasCreator()
    {
        $this->mergeFillable(static::$hasCreatorFillable ?? []);
    }

    public function getCreatableClass()
    {
        // if $this is a table row, fill attributes and relations new class
        if (! $this->creatableClass) {
            return $this;
        }

        $class = new $this->creatableClass;

        $class->setAttribute($this->getKeyName(), $this->getKey());
        $class->fill($this->getAttributes());
        $class->setRelations($this->getRelations());

        return $class;
    }

    /**
     * Get the creator record associated with this model
     */
    public function creatorRecord(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        $creatableClass = $this->getCreatableClass();
        [$type, $id] = $creatableClass->getMorphs('creatable', null, null);
        $creatorRecordModel = new ($this->getCreatorRecordModel());
        $instance = $creatableClass->newRelatedInstance($creatorRecordModel);
        $table = $instance->getTable();
        $localKey = $this->getKeyName();

        return new \Illuminate\Database\Eloquent\Relations\MorphOne(
            $instance->newQuery(),
            $creatableClass,
            $table . '.' . $type,
            $table . '.' . $id,
            $localKey
        );
    }

    /**
     * Get the creator associated with this model through the creator record
     */
    public function creator(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        $creatableClass = $this->getCreatableClass(); // farParent
        $related = $this->getCreatorModel(); // related
        $through = $this->getCreatorRecordModel(); // through
        $throughInstance = $creatableClass->newRelatedThroughInstance($through); // throughInstance
        $relatedQuery = $creatableClass->newRelatedInstance($related)->newQuery(); // relatedQuery

        $relation = new \Illuminate\Database\Eloquent\Relations\HasOneThrough(
            $relatedQuery,
            $creatableClass,
            $throughInstance,
            firstKey: 'creatable_id',
            secondKey: 'id',
            localKey: 'id',
            secondLocalKey: 'creator_id'
        );

        // Add the where condition for creatable_type
        return $relation->where($throughInstance->getTable() . '.creatable_type', get_class($creatableClass));

        // Remove the unreachable code below
        // return $this->hasOneThrough(
        //     related: $this->getCreatorModel(), // User
        //     through: $this->getCreatorRecordModel(), // CreatorRecord
        //     firstKey: 'creatable_id',
        //     secondKey: 'id',
        //     localKey: 'id',
        //     secondLocalKey: 'creator_id'
        // );
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        $creatorRecordModel = new ($this->getCreatorRecordModel());
        $creatorModel = new ($this->getCreatorModel());

        $companyModel = new Company;
        $query = Company::query()
            ->select($companyModel->getTable() . '.*')  // Only select company fields
            ->join(
                $creatorModel->getTable(),
                $creatorModel->getTable() . '.company_id',
                '=',
                $companyModel->getTable() . '.id'
            )
            ->join(
                $creatorRecordModel->getTable(),
                function ($join) use ($creatorRecordModel, $creatorModel) {
                    $join->on($creatorRecordModel->getTable() . '.creator_id', '=', $creatorModel->getTable() . '.id')
                        ->where($creatorRecordModel->getTable() . '.creatable_type', '=', get_class($this->getCreatableClass()))
                        ->where($creatorRecordModel->getTable() . '.creatable_id', '=', $this->id);
                }
            );

        return new \Illuminate\Database\Eloquent\Relations\HasOne(
            $query,
            $this->getCreatableClass(),
            $creatorRecordModel->getTable() . '.creatable_id',
            'id'
        );
    }

    // protected static function getAuthorizedGuardName()
    // {
    //     return self::$authorizedGuardName ?? Modularity::getAuthGuardName();
    // }

    /**
     * Get the creator record model class name
     *
     * @return string The fully qualified class name of the creator record model
     */
    protected function getCreatorRecordModel()
    {
        return \Unusualify\Modularity\Entities\CreatorRecord::class;
    }

    /**
     * Get the creator model class name
     *
     * @return string The fully qualified class name of the creator model
     */
    protected function getCreatorModel()
    {
        $key = $this->getKey();

        try {
            return (! is_null($key) && $this->creatorRecord()->exists()) ? $this->creatorRecord->creator_type : static::getDefaultCreatorModel();
        } catch (\Exception $e) {
            dd($this, $this->creatorRecord);
        }
    }

    /**
     * Get the default creator model class name
     *
     * @return string The fully qualified class name of the default creator model
     */
    public static function getDefaultCreatorModel()
    {
        return static::$defaultHasCreatorModel ?? \App\Models\User::class;
    }

    /**
     * Add authorized query conditions for the creator record
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $user The user to check authorization for
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function addAuthorizedQueryForCreatorRecord($query, $user)
    {
        return $query;
    }

    /**
     * Add authorized user query conditions for the creator record
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $user The user to check authorization for
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function addAuthorizedUserQueryForCreatorRecord($query, $user)
    {
        return $query;
    }

    /**
     * Get the authorized roles for the creator record
     *
     * @return array Array of authorized role names
     */
    protected function getAuthorizedRolesForCreatorRecord()
    {
        return $this->authorizedRolesForCreatorRecord ?? [
            'admin',
            'manager',
            'editor',
        ];
    }

    /**
     * Get the authorized user roles for the creator record
     *
     * @return array Array of authorized user role names
     */
    protected function getAuthorizedUserRolesForCreatorRecord()
    {
        return $this->authorizedUserRolesForCreatorRecord ?? [
            'manager',
            'client-manager',
        ];
    }

    /**
     * Scope a query to only include related creator records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $creator_id
     * @param string|null $guardName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsCreator($query, $creator_id, $guardName = null)
    {
        $guardName ??= Auth::guard()->name;

        return $query->whereHas('creatorRecord', function ($query) use ($creator_id, $guardName) {
            $query->where('creator_id', $creator_id)->where('guard_name', $guardName);
        });
    }

    /**
     * Scope a query to only include the current user's creations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsMyCreation($query)
    {
        $guardName = null;
        $userId = -1;

        if (Auth::check()) {
            $guardName = Auth::guard()->name;
            $userId = Auth::id();
        }

        return $query->whereHas('creatorRecord', function ($query) use ($userId, $guardName) {
            $query->where('creator_id', $userId)
                ->where('guard_name', $guardName);
        });
    }

    public function scopeHasAccessToCreation($query, $user = null, $guardName = null)
    {
        if ($user === null && ! Auth::check()) {
            return $query;
        }

        $guardName ??= Auth::guard()->name;

        $user ??= Auth::guard($guardName)->user();

        $abortRoleExceptions = static::$abortCreatorRoleExceptions ?? false;

        $hasSpatiePermission = in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($user));

        $spatieRoleModel = config('permission.models.role');

        if (! $abortRoleExceptions) {

            if ($hasSpatiePermission) {
                $existingRoles = $spatieRoleModel::whereIn('name', $this->getAuthorizedRolesForCreatorRecord())->get();

                if ($user->isSuperAdmin() || $user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                    return $query;
                }
            }
        }

        return $query->whereHas('creatorRecord', function ($query) use ($user, $hasSpatiePermission, $spatieRoleModel) {

            $query = $this->addAuthorizedQueryForCreatorRecord($query, $user);

            $query = $query->whereHas('creator', function ($query) use ($user, $hasSpatiePermission, $spatieRoleModel) {
                $query = $query->where('id', $user->id);

                if ($hasSpatiePermission) {
                    $existingRoles = $spatieRoleModel::whereIn('name', $this->getAuthorizedUserRolesForCreatorRecord())->get();
                    if ($user->company_id && $user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                        $query = $query->orWhere('company_id', $user->company_id);
                    }
                }

                $query = $this->addAuthorizedUserQueryForCreatorRecord($query, $user);
            });
        });
    }

    /**
     * Scope a query to only include the current user's revisions.
     *
     * @deprecated use scopeHasAccessToCreation instead
     */
    public function scopeAuthorized($query, $guardName = null)
    {
        return $this->scopeHasAccessTo($query, null, $guardName);
    }
}
