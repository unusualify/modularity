<?php

namespace Modules\SystemUser\Repositories;

use Illuminate\Support\Arr;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularity\Entities\UserOauth;

class UserRepository extends Repository
{
    use FilepondsTrait;

    public $exceptRelations = [
        // 'roles'
    ];

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function oauthUser($oauthUser)
    {
        return $this->model->whereEmail($oauthUser->email)->first();
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $oauthUser
     * @param string $provider
     * @return boolean
     */
    public function oauthIsUserLinked($oauthUser, $provider)
    {
        //dd('Is user linked');
        $user = $this->oauthUser($oauthUser);
        return $user->providers()
            ->where(['provider' => $provider, 'oauth_id' => $oauthUser->id])
            ->exists();
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $oauthUser
     * @param string $provider
     * @return \A17\Twill\Models\User
     */
    public function oauthUpdateProvider($oauthUser, $provider)
    {
        $user = $this->model->whereEmail($oauthUser->email)->first();
        $provider = $user->providers()
            ->where(['provider' => $provider, 'oauth_id' => $oauthUser->id])
            ->first();

        $provider->token = $oauthUser->token;
        $provider->avatar = $oauthUser->avatar;
        $provider->save();

        return $user;
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $oauthUser
     * @return \A17\Twill\Models\User
     */
    public function oauthCreateUser($oauthUser)
    {
        // if (config('modularity.oauth.providers')) {
        // $user->assignRole('client-manager');
        //     $defaultRole = config('permission.models.role')::where('name', 'client-manager')->first();
        // $defaultRole = twillModel('role')::where('name', config('twill.oauth.permissions_default_role'))->first();

        // } else {
             //$roleKeyValue = ['role' => config('twill.oauth.default_role')];
        // }

        $fullName = $oauthUser->name;
        $nameWithSurname= name_surname_resolver($fullName);
        $nameArray = array_slice($nameWithSurname, 0, count($nameWithSurname) - 1);
        $name = implode(" ",$nameArray);
        $surname= end($nameWithSurname);
        $email = $oauthUser->email;
        // dd(
        //     [
        //         $name,
        //         $surname,
        //     ]
        //     );

        $user = $this->model->firstOrNew([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'published' => true,
        ] /*+ $roleKeyValue*/);


        $user->save();

        $user->assignRole('client-manager');

        $user->company()->create();

        return $user;
    }

}
