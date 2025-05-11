<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Entities\UserOauth;
use Unusualify\Modularity\Tests\ModelTestCase;

class UserOauthTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $socialiteGoogleOauthUser;

    protected $socialiteGithubOauthUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->socialiteGoogleOauthUser = (new SocialiteUser)->map([
            'id' => uniqid(),
            'nickname' => null,
            'name' => 'Erdem Çelik (celikerde)',
            'email' => 'erdemcelk507@gmail.com',
            'token' => 'ya29.a0AZYkNZgJDemZA3tTfPQcxp8CqVqZQz5IbS-_kIJr02Y4biC8HtC-BhYn6omekwHW8de_MBD-BW_m-ZlsvNtVM6Kk9Fk3jPVsomdZ0Ep0hwPUQJ214puVDKCZnR5Um9sJ5zFC3V3rrQ5rpWv91T6spkBLvyZ-PEYts184By0SBgaCgYKAf4SARASFQHGX2MihqC5IYUl_30d3D0omyRZZA0177',
            'avatar' => 'https://lh3.googleusercontent.com/a-/ALV-UjXg0LeMZiktbW5k4NJ0yFcX36oj7dGc7XkHj_45UEIgwBwzd_73VD2WdyB9dzwelNWL9PjS-Foyak0D9IaWR76tDqf-hPsC176MIelI-kAFygryTrV1q4A',
        ]);

        $this->socialiteGithubOauthUser = (new SocialiteUser)->map([
            'id' => uniqid(),
            'nickname' => 'celikerde',
            'name' => 'Erdem Çelik',
            'email' => 'erdem@unusualgrowth.com',
            'token' => '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',
            'avatar' => 'https://avatars.githubusercontent.com/u/76479640?v=4',
        ]);

    }

    public function test_get_table_user_oauths()
    {
        $userOauth = new UserOauth;
        $this->assertEquals(modularityConfig('tables.user_oauths', 'um_user_oauths'), $userOauth->getTable());
    }

    public function test_user_oauth_belongs_to_user()
    {

        $user = User::factory()->create();
        $userOauth = UserOauth::create([
            'token' => $this->socialiteGoogleOauthUser->token,
            'provider' => 'google',
            'avatar' => $this->socialiteGoogleOauthUser->avatar,
            'oauth_id' => $this->socialiteGoogleOauthUser->id,
            'user_id' => $user->id,
        ]);

        $relation = $userOauth->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertInstanceOf(User::class, $relation->getRelated());

        $this->assertTrue($userOauth->user->is($user));
        $this->assertEquals($user->id, $userOauth->user->id);

    }

    public function test_user_can_have_many_oauth_providers()
    {
        $user = User::factory()->create();

        UserOauth::create([
            'token' => $this->socialiteGoogleOauthUser->token,
            'provider' => 'google',
            'avatar' => $this->socialiteGoogleOauthUser->avatar,
            'oauth_id' => $this->socialiteGoogleOauthUser->id,
            'user_id' => $user->id,
        ]);

        UserOauth::create([
            'token' => $this->socialiteGithubOauthUser->token,
            'provider' => 'github',
            'avatar' => $this->socialiteGithubOauthUser->avatar,
            'oauth_id' => $this->socialiteGithubOauthUser->id,
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $user->providers);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->providers);
        $this->assertInstanceOf(UserOauth::class, $user->providers->first());

        $userWithoutOauthProvider = User::factory()->create();

        $this->assertCount(0, $userWithoutOauthProvider->providers);

    }

    public function test_link_provider()
    {
        $user = User::factory()->create();
        $googleProviderName = 'google';
        $githubProviderName = 'github';

        $googleLinkProviders = $user->linkProvider($this->socialiteGoogleOauthUser, $googleProviderName);
        $this->assertInstanceOf(UserOauth::class, $googleLinkProviders);

        $this->assertEquals($user->id, $googleLinkProviders->user_id);
        $this->assertEquals($this->socialiteGoogleOauthUser->token, $googleLinkProviders->token);
        $this->assertEquals($this->socialiteGoogleOauthUser->avatar, $googleLinkProviders->avatar);
        $this->assertEquals($googleProviderName, $googleLinkProviders->provider);
        $this->assertEquals($this->socialiteGoogleOauthUser->id, $googleLinkProviders->oauth_id);

        $this->assertDatabaseHas((new UserOauth)->getTable(), [
            'user_id' => $user->id,
            'provider' => $googleProviderName,
            'oauth_id' => $this->socialiteGoogleOauthUser->id,
        ]);

        $githubLinkDatas = $user->linkProvider($this->socialiteGithubOauthUser, $githubProviderName);

        $this->assertInstanceOf(UserOauth::class, $githubLinkDatas);

        $this->assertEquals($user->id, $githubLinkDatas->user_id);

        $this->assertEquals($this->socialiteGithubOauthUser->token, $githubLinkDatas->token);
        $this->assertEquals($this->socialiteGithubOauthUser->avatar, $githubLinkDatas->avatar);
        $this->assertEquals($githubProviderName, $githubLinkDatas->provider);
        $this->assertEquals($this->socialiteGithubOauthUser->id, $githubLinkDatas->oauth_id);

        $this->assertDatabaseHas((new UserOauth)->getTable(), [
            'user_id' => $user->id,
            'provider' => $githubProviderName,
            'oauth_id' => $this->socialiteGithubOauthUser->id,
        ]);

        $this->assertDatabaseCount((new UserOauth)->getTable(), 2);
    }

    public function test_can_link_multiple_providers_to_a_user()
    {
        $user = User::factory()->create();

        $user->linkProvider($this->socialiteGithubOauthUser, 'github');
        $user->linkProvider($this->socialiteGoogleOauthUser, 'google');

        $this->assertCount(2, $user->fresh()->providers);
        $this->assertDatabaseHas((new UserOauth)->getTable(), ['provider' => 'github', 'oauth_id' => $this->socialiteGithubOauthUser->id]);
        $this->assertDatabaseHas((new UserOauth)->getTable(), ['provider' => 'google', 'oauth_id' => $this->socialiteGoogleOauthUser->id]);
    }
}
