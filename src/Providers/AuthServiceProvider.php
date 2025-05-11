<?php

namespace Unusualify\Modularity\Providers;

// use Unusualify\Modularity\Models\Enums\UserRole;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Laravel\Horizon\Horizon;
use Spatie\Permission\Models\Permission;
use Unusualify\Modularity\Entities\User;

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
        if (exceptionalRunningInConsole() && database_exists() && Schema::hasTable(config('permission.table_names.permissions'))) {
            Gate::before(function (User $user, $ability) {
                return $user->hasRole(self::SUPERADMIN) ? true : null;
            });

            Gate::define('dashboard', function ($user) {
                return $this->authorize($user, function ($user) {
                    return $this->userHasPermission($user, ['dashboard']);
                    // return $this->userHasRole($user, [UserRole::VIEWONLY, UserRole::PUBLISHER, UserRole::ADMIN]);
                });
            });

            foreach (Permission::all() as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $this->authorize($user, function ($user) use ($permission) {
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

        Horizon::auth(function ($request) {
            // dd($request->user());
            return app()->environment('local') || $request->user()->isSuperAdmin() || in_array($request->user()->email, [
                'software-dev@unusualgrowth.cm',
            ]);
        });
    }

    public function register()
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $verifyUrl = URL::temporarySignedRoute(
                'admin.verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return $verifyUrl;

            dd($verifyUrl);

            return 'https://yourapp.com/email/verify?verify_url=' . urlencode($verifyUrl);
        });

        VerifyEmail::toMailUsing(function ($notifiable, $verificationUrl) {

            // this is what is currently being done
            // adjust for your needs

            // dd($notifiable, $verificationUrl);
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject(Lang::get('Verify Email Address'))
                ->line(Lang::get('Please click the button below to verify your email address.'))
                ->action(
                    Lang::get('Verify Email Address'),
                    $verificationUrl
                )
                ->line(Lang::get('If you did not create an account, no further action is required.'));

        });
    }
}
