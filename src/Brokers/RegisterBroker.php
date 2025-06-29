<?php

namespace Unusualify\Modularity\Brokers;

use Closure;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Unusualify\Modularity\Contracts\RegisterBroker as RegisterBrokerContract;
use Illuminate\Support\Facades\DB;

class RegisterBroker extends PasswordBroker implements RegisterBrokerContract
{
    public function __construct(TokenRepositoryInterface $tokens,UserProvider $users, protected ConnectionInterface $connection, protected array $config)
    {
        parent::__construct($tokens, $users);
    }


    /**
     * Send a password reset link to a user.
     *
     * @param  array  $credentials
     * @param  \Closure|null  $callback
     * @return string
     */
    public function sendVerificationLink(array $credentials, ?Closure $callback = null)
    {
        $user = $this->getUser($credentials);
        if (!is_null($user)) {
            return static::ALREADY_REGISTERED;
        } else {
            $email = $credentials['email'];
            $user = new \Unusualify\Modularity\Entities\User();
            $user->email = $email;

            if ($this->tokens->recentlyCreatedToken($user)) {
                // dd("reset throttled");
                return static::RESET_THROTTLED;
            }

            $token = $this->tokens->create($user);

            if ($callback) {
                return $callback($user, $token) ?? static::VERIFICATION_LINK_SENT;
            }
        }

        $user->sendRegisterNotification($token);
        return static::VERIFICATION_LINK_SENT;

    }

    public function register(array $credentials, Closure $callback)
    {
        $response = $this->validateRegister($credentials);

        if($response === static::VERIFICATION_SUCCESS) {
            $callback($credentials);
            $this->deleteToken($credentials['email']);
        }

        return $response;
    }

    public function validateRegister(array $credentials)
    {
        $email = $credentials['email'];
        $token = $credentials['token'];

        $userIsRegistered = $this->emailIsRegistered($email);

        if($userIsRegistered) {
            return static::ALREADY_REGISTERED;
        } else if (! $userHasValidToken = $this->emailTokenExists($email, $token)){
            return static::INVALID_VERIFICATION_TOKEN;
        }
        return static::VERIFICATION_SUCCESS;

    }

    public function emailIsRegistered($email)
    {
        $isRegistered = DB::table(modularityConfig('tables.users', 'um_users'))->where('email', $email)->exists();

        return $isRegistered;
    }

    public function emailTokenExists($email, $token)
    {
        $record = (array) $this->connection->table($this->config['table'])
            ->where('email', $email)->first();

        return $record &&
               ! $this->tokenExpired($record['created_at']) &&
                 app('hash')->check($token, $record['token']);
    }

    public function tokenExpired($createdAt)
    {
        return Carbon::parse($createdAt)->addSeconds($this->config['expire'] * 60)->isPast();
    }

    public function deleteToken($email)
    {
        $this->connection->table($this->config['table'])
        ->where('email', $email)->delete();
    }
}
