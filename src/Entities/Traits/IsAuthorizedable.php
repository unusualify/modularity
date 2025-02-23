<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Facades\Modularity;

trait IsAuthorizedable
{
    // public static $authorizedGuardName = 'modularity';

    // public $absoluteRolesAuthorized = [
    //     'admin',
    //     'manager',
    // ];

    // public $absoluteUserRolesAuthorized = [
    //     'manager',
    //     'client-manager',
    // ];
    protected static function bootIsAuthorizedable()
    {

        // static::creating(function ($model) {
        //     dd(Auth::check(), Auth::user(), self::$authorizedGuardName, auth(self::$authorizedGuardName)->user());
        // });
        static::created(function ($model) {
            $model->authorized()->create([
                'user_id' => auth(static::getAuthorizedGuardName())->user()->id,
            ]);
        });


        if(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))){
            // Add deleted event handler
            static::forceDeleting(function ($model) {
                // This will automatically delete the associated authorized record
                $model->authorized()->delete();
            });
        }else{
            // Add deleted event handler
            static::deleting(function ($model) {
                // This will automatically delete the associated authorized record
                $model->authorized()->delete();
            });
        }


    }

    public function authorized(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(
            $this->getAuthorizedModel(),
            'authorizedable',
        );
    }

    public function user()
    {
        return $this->hasOneThrough(
            $this->getAuthorizedUserModel(),
            $this->getAuthorizedModel(),
            'authorizedable_id',
            'id',
            'id',
            'user_id'
        );
    }

    protected static function getAuthorizedGuardName()
    {
        return self::$authorizedGuardName ?? Modularity::getAuthGuardName();
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

    protected function getAbsoluteRolesAuthorized()
    {
        return $this->absoluteRolesAuthorized ?? [
            'admin',
            'manager',
            'editor',
        ];
    }

    protected function getAbsoluteUserRolesAuthorized()
    {
        return $this->absoluteUserRolesAuthorized ?? [
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
    public function scopeAuthorized($query)
    {
        if (! Auth::check()) {
            return $query;
        }

        $user = auth($this->getAuthorizedGuardName())->user();

        if ($user->isSuperAdmin() || $user->hasRole($this->getAbsoluteRolesAuthorized())) {
            return $query;
        }

        return $query->whereHas('authorized', function ($query) use ($user) {

            $query = $this->addAuthorizedQuery($query, $user);

            $query = $query->whereHas('user', function ($query) use ($user) {
                $query = $query->where('id', $user->id);

                if ($user->hasRole($this->getAbsoluteUserRolesAuthorized())) {
                    $query = $query->orWhere('company_id', $user->company_id);
                }

                $query = $this->addAuthorizedUserQuery($query, $user);
            });
        });
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        $authorizedModel = new ($this->getAuthorizedModel());
        $userModel = new ($this->getAuthorizedUserModel());
        $companyModel = new Company;
        $query = Company::query()
            ->select($companyModel->getTable() . '.*')  // Only select company fields
            ->join(
                $userModel->getTable(),
                $userModel->getTable() . '.company_id',
                '=',
                'umod_companies.id'
            )
            ->join(
                $authorizedModel->getTable(),
                function ($join) use ($authorizedModel, $userModel) {
                    $join->on($authorizedModel->getTable() . '.user_id', '=', $userModel->getTable() . '.id')
                        ->where($authorizedModel->getTable() . '.authorizedable_type', '=', get_class($this))
                        ->where($authorizedModel->getTable() . '.authorizedable_id', '=', $this->id);
                }
            );

        return new \Illuminate\Database\Eloquent\Relations\HasOne(
            $query,
            $this,
            $authorizedModel->getTable() . '.authorizedable_id',
            'id'
        );
    }
}
