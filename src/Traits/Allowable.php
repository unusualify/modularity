<?php

namespace Unusualify\Modularity\Traits;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait Allowable
{
    /**
     * The user to check if the items are allowable for
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public $allowableUser;

    /**
     * Set the allowable user
     */
    public function setAllowableUser($user = null)
    {
        if ($user) {
            $this->allowableUser = $user;

            return;
        }

        $guard = $this->allowableUserGuard ?? null;

        if ($guard) {
            if (Auth::guard($guard)->check()) {
                $this->allowableUser = Auth::guard($guard)->user();
            }
        } else {
            if (Auth::check()) {
                $this->allowableUser = Auth::user();
            }
        }
    }

    /**
     * Get the allowable items for the current user
     *
     * @param array|Collection $items
     * @param string|null $searchKey
     * @param Closure|null $orClosure
     * @param Closure|null $andClosure
     */
    public function getAllowableItems($items, $searchKey = null, $orClosure = null, $andClosure = null): array|Collection
    {
        if (! $orClosure) {
            $orClosure = fn ($item, $user) => false;
        }

        if (! $andClosure) {
            $andClosure = fn ($item, $user) => true;
        }

        $isArray = is_array($items);

        if ($isArray) {
            $items = collect($items);
        } elseif (! $items instanceof Collection) {
            throw new \Exception('Invalid items type, must be an array or a collection');
        }

        $searchKey = $searchKey ?? $this->allowedRolesSearchKey ?? 'allowedRoles';

        $newItems = $items->reduce(function ($carry, $item) use ($searchKey, $orClosure, $andClosure) {

            if ($this->isAllowedItem($item, $searchKey, $orClosure, $andClosure)) {
                $carry->push($item);
            }

            return $carry;
        }, collect([]));

        if ($isArray) {
            return $newItems->toArray();
        }

        return $newItems;
    }

    /**
     * Check if the item is allowed for the current user
     *
     * @param array|object $item
     * @param string|null $searchKey
     * @param Closure|null $orClosure
     * @param Closure|null $andClosure
     * @param bool $disallowIfUnauthenticated
     * @return bool
     */
    public function isAllowedItem($item, $searchKey = null, $orClosure = null, $andClosure = null, $disallowIfUnauthenticated = true)
    {
        if (! $this->allowableUser) {
            $this->setAllowableUser();
        }

        if (! $orClosure) {
            $orClosure = fn ($item, $user) => false;
        }

        if (! $andClosure) {
            $andClosure = fn ($item, $user) => true;
        }

        $searchKey = $searchKey ?? $this->allowedRolesSearchKey ?? 'allowedRoles';

        if (! $this->allowableUser) {
            return ! $disallowIfUnauthenticated;
        }

        if ($andClosure($item, $this->allowableUser)) {
            $itemType = is_array($item) ? 'array' : 'object';
            $hasSearchKey = $itemType == 'array' ? isset($item[$searchKey]) : isset($item->{$searchKey});
            $allowedRoles = $itemType == 'array' ? ($item[$searchKey] ?? []) : ($item->{$searchKey} ?? []);

            if (! $hasSearchKey) {
                return true;
            } else {
                $allowedRoles = is_array($allowedRoles) ? $allowedRoles : explode(',', $allowedRoles);

                if ($orClosure($item, $this->allowableUser) || $this->allowableUser->hasRole($allowedRoles)) {
                    return true;
                }
            }
        }

        return false;
    }
}
