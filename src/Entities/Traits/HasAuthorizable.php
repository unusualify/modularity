<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Authorization;
use Unusualify\Modularity\Traits\Allowable;

trait HasAuthorizable
{
    use Allowable;
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
                    ?? ($model->authorizationRecord()->exists()
                        ? $model->authorizationRecord->authorized_type
                        : $model->getDefaultAuthorizedModel());

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
    public function initializeHasAuthorizable(): void
    {
        $this->mergeFillable(static::$hasAuthorizableFillable);
    }

    /**
     * Get the authorization record associated with this model
     */
    public function authorizationRecord(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Authorization::class, 'authorizable');
    }

    /**
     * Get the authorized user associated with this model through the authorization record
     */
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

    protected function isAuthorized(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->authorizedUser()->exists();
            }
        );
    }

    /**
     * Get the authorized model class from the authorization record or default
     *
     * @return string The fully qualified class name of the authorized model
     *
     * @throws \Exception If there's an error retrieving the model
     */
    final public function getAuthorizedModel()
    {
        return $this->authorizationRecord()->exists()
            ? ($this->authorizationRecord ? $this->authorizationRecord->authorized_type : $this->getDefaultAuthorizedModel())
            : $this->getDefaultAuthorizedModel();
    }

    /**
     * Get the default authorized model class name
     *
     * @return string The fully qualified class name of the default authorized model
     */
    public static function getDefaultAuthorizedModel()
    {
        return static::$defaultAuthorizedModel ?? \App\Models\User::class;
    }

    public function getUserForHasAuthorization($user = null)
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
     * Scope query to only include records authorized for the given user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null $user The user to check authorization for (defaults to authenticated user)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasAuthorization($query, $user = null)
    {
        if (! ($user = $this->getUserForHasAuthorization($user))) {
            return $query;
        }

        if (in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($user))) {
            // Get roles to check from model's static property if defined
            $rolesToCheck = static::$authorizableRolesToCheck ?? null;

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

        return $query->whereHas('authorizationRecord', function ($query) use ($user) {
            $query->where('authorized_id', $user->id)
                ->where('authorized_type', get_class($user));
        });
    }

    /**
     * Check if the current user has authorization usage
     *
     * @return bool
     */
    public function hasAuthorizationUsage($user = null)
    {
        if (! ($user = $this->getUserForHasAuthorization($user))) {
            return false;
        }

        $this->allowableUser = $user;

        return $this->isAllowedItem(
            item: $this,
            searchKey: 'allowedRolesForAuthorizationManagement',
            disallowIfUnauthenticated: false
        );
    }

    public function scopeIsAuthorizedToYou($query, $user = null)
    {
        $authorizedUserId = -1;
        $authorizedUserType = null;
        if (($user = $this->getUserForHasAuthorization($user))) {
            $authorizedUserId = $user->id;
            $authorizedUserType = get_class($user);
        }

        return $query->whereHas('authorizationRecord', function ($query) use ($authorizedUserId, $authorizedUserType) {
            $query->where('authorized_id', $authorizedUserId)
                ->where('authorized_type', $authorizedUserType);
        });
    }

    public function scopeIsAuthorizedToYourRole($query, $user = null)
    {
        $userModel = $this->getAuthorizedModel();
        $userRoles = [];
        if ($user = $this->getUserForHasAuthorization($user)) {
            $userModel = get_class($user);
            $userRoles = $user->roles;
        }

        return $query->whereHas('authorizationRecord', function ($query) use ($userModel, $userRoles) {
            $query->where('authorized_type', $userModel)
                ->whereHas('authorized', function ($query) use ($userRoles) {
                    $query->role($userRoles);
                });
        });
    }

    public function scopeHasAnyAuthorization($query)
    {
        return $query->whereHas('authorizationRecord', function ($query) {
            $query->whereNotNull('authorized_id')
                ->whereNotNull('authorized_type');
        });
    }

    public function scopeUnauthorized($query)
    {
        return $query->whereDoesntHave('authorizationRecord', function ($query) {
            $query->whereNotNull('authorized_id')
                ->whereNotNull('authorized_type');
        });
    }
}
