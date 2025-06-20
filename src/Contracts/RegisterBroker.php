<?php

namespace Unusualify\Modularity\Contracts;

use Closure;
use Illuminate\Contracts\Auth\PasswordBroker;

interface RegisterBroker extends PasswordBroker
{
    /**
     * Send a verification link to a user.
     *
     * @param  array  $credentials
     * @param  \Closure|null  $callback
     * @return string
     */

    const VERIFICATION_LINK_SENT = 'verifications.sent';

    const ALREADY_REGISTERED = 'verifications.exists';

    const VERIFICATION_SUCCESS = 'verifications.success';

    const INVALID_VERIFICATION_TOKEN = 'verifications.token';

    const VERIFICATION_THROTTLED = 'verifications.throttled';


}
