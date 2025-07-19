<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

trait ApiAuthentication
{
    /**
     * API authentication guard
     *
     * @var string
     */
    protected $apiGuard = 'sanctum';

    /**
     * Check if user is authenticated for API
     *
     * @return bool
     */
    protected function isApiAuthenticated(): bool
    {
        return Auth::guard($this->apiGuard)->check();
    }

    /**
     * Get authenticated API user
     *
     * @return \Illuminate\Foundation\Auth\User|null
     */
    protected function getApiUser()
    {
        return Auth::guard($this->apiGuard)->user();
    }

    /**
     * Require authentication for API endpoints
     *
     * @return JsonResponse|void
     */
    protected function requireApiAuthentication()
    {
        if (!$this->isApiAuthenticated()) {
            return $this->respondUnauthorized();
        }
    }

    /**
     * Check if user has permission for API action
     *
     * @param string $permission
     * @return bool
     */
    protected function hasApiPermission(string $permission): bool
    {
        $user = $this->getApiUser();

        if (!$user) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Require permission for API action
     *
     * @param string $permission
     * @return JsonResponse|void
     */
    protected function requireApiPermission(string $permission)
    {
        if (!$this->hasApiPermission($permission)) {
            return $this->respondForbidden();
        }
    }
}
