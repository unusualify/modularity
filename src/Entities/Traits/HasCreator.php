<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Facades\Modularity;

trait HasCreator
{
    protected $isCustomCreatorSaving = false;

    protected static $hasCreatorFillable = ['custom_creator_id', 'custom_creator_type', 'custom_guard_name'];

    protected $customHasCreatorFields = [];

    protected static function bootHasCreator()
    {
        static::created(function ($model) {
            if (Auth::check()) {
                $guard = Auth::guard();

                if ($model->isCustomCreatorSaving) {
                    $model->creatorRecord()->create($model->customHasCreatorFields);
                    $model->isCustomCreatorSaving = false;
                    $model->customHasCreatorFields = [];
                } else {
                    $model->creatorRecord()->create([
                        'creator_id' => $guard->id(), // creator user id
                        'creator_type' => $guard->getProvider()->getModel(), // creator model
                        'guard_name' => $guard->name,
                    ]);
                }

            }
        });

        static::saving(function ($model) {
            if (Auth::check() && $model->custom_creator_id) {
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

    public function creatorRecord(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(
            $this->getCreatorRecordModel(),
            'creatable',
        );
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            $this->getCreatorModel(),
            $this->getCreatorRecordModel(),
            'creatable_id',
            'id',
            'id',
            'creator_id'
        );
    }

    // protected static function getAuthorizedGuardName()
    // {
    //     return self::$authorizedGuardName ?? Modularity::getAuthGuardName();
    // }

    protected function getCreatorRecordModel()
    {
        return \Unusualify\Modularity\Entities\CreatorRecord::class;
    }

    protected function getCreatorModel()
    {
        $key = $this->getKey();

        try {
            return (! is_null($key) && $this->creatorRecord()->exists()) ? $this->creatorRecord->creator_type : static::getDefaultCreatorModel();
        } catch (\Exception $e) {
            dd($this, $this->creatorRecord);
        }
    }

    public static function getDefaultCreatorModel()
    {
        return static::$defaultHasCreatorModel ?? \App\Models\User::class;
    }

    protected function addAuthorizedQueryForCreatorRecord($query, $user)
    {
        return $query;
    }

    protected function addAuthorizedUserQueryForCreatorRecord($query, $user)
    {
        return $query;
    }

    protected function getAuthorizedRolesForCreatorRecord()
    {
        return $this->authorizedRolesForCreatorRecord ?? [
            'admin',
            'manager',
            'editor',
        ];
    }

    protected function getAuthorizedUserRolesForCreatorRecord()
    {
        return $this->authorizedUserRolesForCreatorRecord ?? [
            'manager',
            'client-manager',
        ];
    }

    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuthorized($query, $guardName = null)
    {
        if (! Auth::check()) {
            return $query;
        }

        $guardName ??= Auth::guard()->name;

        $user = auth($guardName)->user();

        $hasSpatiePermission = in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($user));
        $spatieRoleModel = config('permission.models.role');

        if ($hasSpatiePermission) {
            $existingRoles = $spatieRoleModel::whereIn('name', $this->getAuthorizedRolesForCreatorRecord())->get();

            if ($user->isSuperAdmin() || $user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                return $query;
            }
        }

        return $query->whereHas('creatorRecord', function ($query) use ($user, $hasSpatiePermission, $spatieRoleModel) {

            $query = $this->addAuthorizedQueryForCreatorRecord($query, $user);

            $query = $query->whereHas('creator', function ($query) use ($user, $hasSpatiePermission, $spatieRoleModel) {
                $query = $query->where('id', $user->id);

                if ($hasSpatiePermission) {
                    $existingRoles = $spatieRoleModel::whereIn('name', $this->getAuthorizedUserRolesForCreatorRecord())->get();
                    if ($user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                        $query = $query->orWhere('company_id', $user->company_id);
                    }
                }

                $query = $this->addAuthorizedUserQueryForCreatorRecord($query, $user);
            });
        });
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
                        ->where($creatorRecordModel->getTable() . '.creatable_type', '=', get_class($this))
                        ->where($creatorRecordModel->getTable() . '.creatable_id', '=', $this->id);
                }
            );

        return new \Illuminate\Database\Eloquent\Relations\HasOne(
            $query,
            $this,
            $creatorRecordModel->getTable() . '.creatable_id',
            'id'
        );
    }
}
