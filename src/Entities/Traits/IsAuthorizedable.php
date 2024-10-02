<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Auth;

trait IsAuthorizedable
{
    public static $authorizedGuardName = 'unusual_users';

    public $allAuthorizedRoles = [
        'admin',
        'manager',
        'editor',
    ];

    public $authorizedUserRoles = [
        'manager',
        'client-manager',
    ];

    protected static function bootIsAuthorizedable()
    {
        static::created(function ($model) {
            $model->authorized()->create([
                'user_id' => auth(self::$authorizedGuardName)->user()->id,
            ]);
        });
    }

    public function authorized(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(
            $this->getAuthorizedModel(),
            'authorizedable',
        );
    }

    protected function getAuthorizedModel()
    {
        return \Unusualify\Modularity\Entities\Authorized::class;
    }

    protected function getAuthorizedUserModel()
    {
        return \Unusualify\Modularity\Entities\User::class;
    }

    protected function addAuthorizedQuery($query, $user)
    {
        return $query;
    }

    protected function addAuthorizedUserQuery($query, $user)
    {
        return $query;
    }

    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuthorized($query)
    {
        if (! Auth::check()) {
            return $query;
        }

        $user = auth($this->authorizedGuardName)->user();

        if ($user->isSuperAdmin() || $user->hasRole($this->allAuthorizedRoles)) {
            return $query;
        }

        return $query->whereHas('authorized', function ($query) use ($user) {

            $query = $this->addAuthorizedQuery($query, $user);

            $query = $query->whereHas('user', function ($query) use ($user) {
                $query = $query->where('id', $user->id);

                if ($user->hasRole($this->authorizedUserRoles)) {
                    $query = $query->orWhere('company_id', $user->company_id);
                }

                $query = $this->addAuthorizedUserQuery($query, $user);
            });
        });
    }
}
