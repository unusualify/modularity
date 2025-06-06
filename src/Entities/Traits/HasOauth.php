<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Modularity\Entities\UserOauth;

trait HasOauth
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function providers()
    {

        return $this->hasMany(UserOauth::class, 'user_id');

    }

    /**
     * @param string $provider Socialite provider
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function linkProvider(\Laravel\Socialite\Contracts\User $oauthUser, $provider)
    {

        $provider = new UserOauth([
            'token' => $oauthUser->token,
            'avatar' => $oauthUser->avatar,
            'provider' => $provider,
            'oauth_id' => $oauthUser->id,
        ]);

        return $this->providers()->save($provider);
    }
}
