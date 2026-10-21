<?php

namespace Unusualify\Modularous\Providers;

// use Unusualify\Modularous\Models\Enums\UserRole;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Laravel\Horizon\Horizon;
use Spatie\Permission\Models\Permission;
use Unusualify\Modularous\Entities\User;

class AuthServiceProvider extends ServiceProvider implements DeferrableProvider
{
    const SUPERADMIN = 'superadmin';

    protected function authorize($user, $callback)
    {
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
                    return $user->hasPermission('dashboard');
                    // return $this->userHasRole($user, [UserRole::VIEWONLY, UserRole::PUBLISHER, UserRole::ADMIN]);
                });
            });

            foreach (Permission::all() as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $this->authorize($user, function ($user) use ($permission) {
                        // return $this->userHasPermission($user, [$permission->name]);
                        return $user->hasPermission($permission->name);
                        // return $this->userHasRole($user, [UserRole::VIEWONLY, UserRole::PUBLISHER, UserRole::ADMIN]);
                    });
                });
            }

            Gate::define('impersonate', function ($user) {
                return (bool) $user->is_superadmin;
            });
        }

        Horizon::auth(function ($request) {
            // dd($request->user());
            return app()->environment('local') || $request->user()->is_superadmin || in_array($request->user()->email, [
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
            return (new MailMessage)
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
