<?php

namespace Unusualify\Modularity\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Unusualify\Modularity\Entities\User;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject(Lang::get('Reset your :appName password', ['appName' => config('app.name')]))
            ->greeting(Lang::get('Hi :userName,', ['userName' => $notifiable->name]))
            ->line(Lang::get('We got a request to reset the password for your :appName account, and we\'re here to help. Just click the button below to create a new password.', ['appName' => config('app.name')]))
            ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get('This link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('If you didn\'t ask for a password reset, you can safely ignore this email.'))
            ->salutation(Lang::get('Regards, :appName Support', ['appName' => config('app.name')]));
    }
}
