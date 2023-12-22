<?php

namespace Unusualify\Modularity\Providers;

// use Unusualify\Modularity\Models\Enums\UserRole;

use Illuminate\Contracts\Support\DeferrableProvider;
use Unusualify\Modularity\Entities\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider implements DeferrableProvider
{
    const SUPERADMIN = 'superadmin';

    protected function authorize($user, $callback)
    {
        // if (!$user->isPublished()) {
        //     return false;
        // }

        // if ($user->isSuperAdmin()) {
        //     return true;
        // }

        return $callback($user);
    }

    protected function userHasRole($user, $roles)
    {
        // dd($user->roles);
        return in_array($user->roles, $roles);
    }

    protected function userHasPermission($user, $permissions)
    {
        return in_array($user->permissions, $permissions);
    }

    public function boot()
    {
        if(!$this->app->runningInConsole() && database_exists() && Schema::hasTable(config('permission.table_names.permissions'))){
            Gate::before(function (User $user, $ability) {
                return $user->hasRole(self::SUPERADMIN) ? true : null;
            });

            Gate::define('dashboard', function ($user) {
                return $this->authorize($user, function ($user){
                    return $this->userHasPermission($user, ['dashboard']);
                    // return $this->userHasRole($user, [UserRole::VIEWONLY, UserRole::PUBLISHER, UserRole::ADMIN]);
                });
            });

            foreach( Permission::all() as $permission ){
                Gate::define($permission->name, function ($user) use($permission) {
                    return $this->authorize($user, function ($user) use($permission){
                        return $this->userHasPermission($user, [$permission->name]);
                        // return $this->userHasRole($user, [UserRole::VIEWONLY, UserRole::PUBLISHER, UserRole::ADMIN]);
                    });
                });
            }

            // Gate::define('press-release_access', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasPermission($user, ['press-release_access']);
            //         // return $this->userHasRole($user, [UserRole::VIEWONLY, UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('list', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::VIEWONLY, UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('edit', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('reorder', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('publish', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('feature', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('delete', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('duplicate', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('upload', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::PUBLISHER, UserRole::ADMIN]);
            //     });
            // });

            // Gate::define('manage-users', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         return $this->userHasRole($user, [UserRole::ADMIN]);
            //     });
            // });

            // // As an admin, I can edit users, except superadmins
            // // As a non-admin, I can edit myself only
            // Gate::define('edit-user', function ($user, $editedUser = null) {
            //     return $this->authorize($user, function ($user) use ($editedUser) {
            //         $editedUserObject = User::find($editedUser);
            //         return ($this->userHasRole($user, [UserRole::ADMIN]) || $user->id == $editedUser)
            //             && ($editedUserObject ? $editedUserObject->role !== self::SUPERADMIN : true);
            //     });
            // });

            // Gate::define('publish-user', function ($user) {
            //     return $this->authorize($user, function ($user) {
            //         $editedUserObject = User::find(request('id'));
            //         return $this->userHasRole($user, [UserRole::ADMIN]) && ($editedUserObject ? $user->id !== $editedUserObject->id && $editedUserObject->role !== self::SUPERADMIN : false);
            //     });
            // });

            Gate::define('impersonate', function ($user) {
                return $user->role === self::SUPERADMIN;
            });
        }

    }
}
