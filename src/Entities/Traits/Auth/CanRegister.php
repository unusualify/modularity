<?php

namespace Unusualify\Modularity\Entities\Traits\Auth;

use Unusualify\Modularity\Notifications\EmailVerification;

trait CanRegister
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForRegister()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendRegisterNotification($token, $parameters = [])
    {
        $this->notify(new EmailVerification($token, $parameters));
    }
}
