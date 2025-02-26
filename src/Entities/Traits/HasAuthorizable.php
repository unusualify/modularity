<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Authorization;

trait HasAuthorizable
{
    // protected $defaultAuthorizedModel = \App\Models\User::class;

    protected static $hasAuthorizableFillable = ['authorized_id', 'authorized_type'];

    protected $modelIsAuthorizing = false;

    protected $hasAuthorizableFields = [];

    /**
     * Perform any actions when booting the trait
     */
    public static function bootHasAuthorizable(): void
    {
        static::retrieved(function (Model $model) {
            if ($model->authorizationRecord()->exists()) {
                $model->authorized_id = $model->authorizationRecord->authorized_id;
                $model->authorized_type = $model->authorizationRecord->authorized_type;

                $authorizedModel = new $model->authorized_type;

                if (! in_array('Unusualify\Modularity\Entities\Traits\HasUuid', class_uses_recursive($authorizedModel))) {
                    $model->authorized_id = intval($model->authorized_id);
                }
            }
        });

        static::updated(function (Model $model) {
            if ($model->modelIsAuthorizing) {
                $model->authorizationRecord()->updateOrCreate(
                    [], // Empty array as we want to update/create based on the relationship
                    $model->hasAuthorizableFields
                );
                $model->modelIsAuthorizing = false;
                $model->hasAuthorizableFields = [];
            }
        });

        static::saving(function (Model $model) {
            if ($model->authorized_id) {
                $authorizedType = $model->authorized_type
                    ?? $model->authorizationRecord()->exists()
                        ? $model->authorizationRecord->authorized_type
                        : $model->getDefaultAuthorizedModel();

                if (class_exists($authorizedType)) {

                    $authorizedExists = $authorizedType::whereId($model->authorized_id)->exists();

                    if ($authorizedExists) {
                        $model->modelIsAuthorizing = true;
                        $model->hasAuthorizableFields = [
                            'authorized_id' => $model->authorized_id,
                            'authorized_type' => $authorizedType,
                        ];
                    }
                }
            }

            foreach (static::$hasAuthorizableFillable as $field) {
                $model->offsetUnset($field);
            }
        });

        static::saved(function (Model $model) {
            if ($model->modelIsAuthorizing) {
                $model->authorizationRecord()->updateOrCreate(
                    [], // Empty array as we want to update/create based on the relationship
                    $model->hasAuthorizableFields
                );
                $model->modelIsAuthorizing = false;
                $model->hasAuthorizableFields = [];
            }
        });

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::forceDeleting(function (Model $model) {
                $model->authorizationRecord()->delete();
            });
        } else {
            static::deleting(function (Model $model) {
                $model->authorizationRecord()->delete();
            });
        }

    }

    /**
     * Laravel hook to initialize the trait
     */
    public function initializeHasAuthorizable(): void {}

    public function authorizationRecord(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Authorization::class, 'authorizable');
    }

    public function authorizedUser(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            $this->getAuthorizedModel(),
            Authorization::class,
            'authorizable_id',
            'id',
            'id',
            'authorized_id'
        );
    }

    final public function getAuthorizedModel()
    {
        try {
            return $this->authorizationRecord()->exists()
                ? $this->authorizationRecord->authorized_type
                : $this->getDefaultAuthorizedModel();
        } catch (\Exception $e) {
            dd($this, $this->authorizationRecord, $e);
        }
    }

    public static function getDefaultAuthorizedModel()
    {
        return static::$defaultAuthorizedModel ?? \App\Models\User::class;
    }

    public static function getObligatoryAuthorizationRoles()
    {
        return static::$obligatoryAuthorizationRoles ?? ['superadmin', 'admin'];
    }

    public function scopeHasAuthorization($query, $user = null)
    {
        if (! Auth::check()) {
            return $query;
        }

        $user = $user ?? Auth::user();

        if (! $user) {
            return $query;
        }

        if (in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($user))) {
            $roleModel = config('permission.models.role');
            $existingRoles = $roleModel::whereIn('name', $this->getObligatoryAuthorizationRoles())->get();
            if ($user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                return $query;
            }
        }

        return $query->whereHas('authorizationRecord', function ($query) use ($user) {
            $query->where('authorized_id', $user->id)
                ->where('authorized_type', get_class($user));
        });
    }
}
