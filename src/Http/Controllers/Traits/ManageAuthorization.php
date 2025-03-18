<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;

trait ManageAuthorization
{
    /**
     * Check if the user is super admin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->user && $this->user->isSuperAdmin();
    }

    /**
     * Check if the user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return isset($this->user) && $this->user;
    }

    /**
     * Check if the user has authorization to perform an action
     *
     * @param array $roles
     * @return bool
     */
    public function isAuthorized($roles)
    {
        if (! $this->isAuthenticated()) {
            return false;
        }

        return $this->user->hasAnyRole($roles);
    }

    /**
     * Check if the user has authorization to perform any action
     *
     * @param array $roles
     * @return bool
     */
    public function hasAuthorization($roles)
    {
        return $this->isAuthorized($roles);
    }

    /**
     * Check if the user has authorization to perform any action
     *
     * @param array $roles
     * @return bool
     */
    public function doesNotHaveAuthorization($roles)
    {
        return !$this->isAuthorized($roles);
    }
}