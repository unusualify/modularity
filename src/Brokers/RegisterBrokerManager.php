<?php

namespace Unusualify\Modularity\Brokers;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use InvalidArgumentException;

/**
 * @mixin \Illuminate\Contracts\Auth\PasswordBroker
 */
class RegisterBrokerManager extends PasswordBrokerManager
{
    public function __construct($app)
    {
        parent::__construct($app);
    }

    public function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Email verification broker [{$name}] is not defined.");
        }
        // dd($config, $this->app['db']->connection($config['connection'] ?? null));
        // The password broker uses a token repository to validate tokens and send user
        // password e-mails, as well as validating that password reset process as an
        // aggregate service of sorts providing a convenient interface for resets.

        return new RegisterBroker(
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'] ?? null),
            $this->app['db']->connection($config['connection'] ?? null),
            $config,

        );
    }

    /**
     * Get the default password broker name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'register_verified_users';
    }
}
