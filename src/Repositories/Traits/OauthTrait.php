<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait OauthTrait
{

    /**
     * @param \Laravel\Socialite\Contracts\User $oauthUser
     * @return \Unusualify\Modularity\Entities\User
     */
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
        $user = $this->model->whereEmail($oauthUser->email)->first();

        return $user->providers()
            ->where(['provider' => $provider, 'oauth_id' => $oauthUser->id])
            ->exists();
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $oauthUser
     * @param string $provider
     * @return \Unusualify\Modularity\Entities\User
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
     * @return \Unusualify\Modularity\Entities\User
     */
    public function oauthCreateUser($oauthUser)
    {

        $user = $this->model->firstOrNew([
            'name' => $oauthUser->name,
            'email' => $oauthUser->email,
            'role' => config(unusualBaseKey() . '.oauth.default_role'),
            'published' => true,
        ]);

        $user->save();

        return $user;

    }

}
