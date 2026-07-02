<?php

namespace Unusualify\Modularity\Entities\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Authorization;
use Unusualify\Modularity\Traits\Allowable;

trait HasAuthorizable
{
    use Allowable;
    // protected $defaultAuthorizedModel = \App\Models\User::class;

    protected static $hasAuthorizableFillable = ['authorized_id', 'authorized_type'];

    protected $modelIsAuthorizing = false;

    protected $modelIsUnauthorizing = false;

    protected $hasAuthorizableFields = [];

    /**
     * Perform any actions when booting the trait
     */
    public static function bootHasAuthorizable(): void
    {
        static::saving(function (Model $model) {
            // Whether the authorized_id field was explicitly provided in this save (e.g. form submission).
            // This lets us tell an intentional "clear" (empty value submitted) apart from saves
            // that don't touch authorization at all (key absent).
            $authorizedIdProvided = array_key_exists('authorized_id', $model->getAttributes());

            if ($model->authorized_id) {
                $authorizedType = $model->authorized_type
                    ?? ($model->hasAuthorizationRecord()
                        ? (($model->relationLoaded('authorizationRecord')
                            ? $model->authorizationRecord->authorized_type
                            : $model->authorizationRecord()->value('authorized_type')) ?? $model->getDefaultAuthorizedModel())
                        : $model->getDefaultAuthorizedModel());

                if (class_exists($authorizedType)) {

                    $authorizedExists = $authorizedType::whereId($model->authorized_id)->exists();

                    $currentAuthorizedId = $model->hasAuthorizationRecord()
                        ? ($model->relationLoaded('authorizationRecord')
                            ? $model->authorizationRecord->authorized_id
                            : $model->authorizationRecord()->value('authorized_id'))
                        : null;

                    if ($authorizedExists && (string) $currentAuthorizedId !== (string) $model->authorized_id) {
                        $model->modelIsAuthorizing = true;
                        $model->hasAuthorizableFields = [
                            'authorized_id' => $model->authorized_id,
                            'authorized_type' => $authorizedType,
                        ];
                    }
                }
            } elseif ($authorizedIdProvided && $model->hasAuthorizationRecord()) {
                $model->modelIsUnauthorizing = true;
            }

            foreach (static::$hasAuthorizableFillable as $field) {
                $model->offsetUnset($field);
            }

            // Force a timestamp so the UPDATE runs. Using updated() (not saved()) guarantees this work runs
            // BEFORE listeners on the saved event (e.g. CacheObserver), which rebuild caches and must
            // observe the authorization record already written/removed.
            if (($model->modelIsAuthorizing || $model->modelIsUnauthorizing)
                && $model->exists
                && $model->usesTimestamps()
                && ! is_null($model->getUpdatedAtColumn())) {
                $model->{$model->getUpdatedAtColumn()} = $model->freshTimestamp();
            }
        });

        static::updated(function (Model $model) {
            // dump('HasAuthorizable: updated');
            if ($model->modelIsAuthorizing) {
                $model->authorizationRecord()->updateOrCreate(
                    [], // Empty array as we want to update/create based on the relationship
                    $model->hasAuthorizableFields
                );
                $model->modelIsAuthorizing = false;
                $model->hasAuthorizableFields = [];
            } elseif ($model->modelIsUnauthorizing) {
                $model->authorizationRecord()->delete();
                $model->modelIsUnauthorizing = false;
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

    public static function addGlobalScopesHasAuthorizable()
    {
        return [
            'authorization_record_exists' => [
                'scope' => function ($query) {
                    $query->withExists('authorizationRecord');
                },
                'count' => false,
            ],
        ];
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
    public function authorizationRecord(): MorphOne
    {
        return $this->morphOne(Authorization::class, 'authorizable');
    }

    /**
     * Pre-computed flag from withExists('authorizationRecord') in the fetch query.
     * Avoids lazy load when checking if authorization record exists.
     */
    protected function authorizationRecordExists(): Attribute
    {
        return Attribute::get(function (?int $value) {
            return $value !== null ? (bool) $value : $this->authorizationRecord()->exists();
        });
    }

    /**
     * Check if authorization record exists without triggering a lazy load when
     * the model was fetched with withExists('authorizationRecord') (via global scope).
     */
    protected function hasAuthorizationRecord(): bool
    {
        return $this->authorization_record_exists ?? false;
    }

    /**
     * Get the authorized user associated with this model through the authorization record
     */
    public function authorizedUser(): HasOneThrough
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
            get: fn ($value) => $value ?? $this->authorization_record_exists ?? false,
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
        if (! $this->hasAuthorizationRecord()) {
            return $this->getDefaultAuthorizedModel();
        }

        return $this->authorizationRecord?->authorized_type ?? $this->getDefaultAuthorizedModel();
    }

    /**
     * Get the default authorized model class name
     *
     * @return string The fully qualified class name of the default authorized model
     */
    public static function getDefaultAuthorizedModel()
    {
        return static::$defaultAuthorizedModel ?? User::class;
    }

    public function getUserForHasAuthorization($user = null)
    {
        return $user ?? Auth::user();
    }

    /**
     * Scope query to only include records authorized for the given user
     *
     * @param Builder $query
     * @param mixed|null $user The user to check authorization for (defaults to authenticated user)
     * @return Builder
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
                // $roleModel = config('permission.models.role');
                // $existingRoles = $roleModel::whereIn('name', $rolesToCheck)->get();

                // if (! $user->hasRole($existingRoles->map(fn ($role) => $role->name)->toArray())) {
                if (! $user->roles->contains(fn ($role) => in_array($role->name, $rolesToCheck))) {
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
