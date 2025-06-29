<?php

namespace Unusualify\Modularity\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

class EmailVerification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        return (new MailMessage)
            ->subject(Lang::get('Email Verification'))
            ->line(Lang::get('You are receiving this email because we need to verify your email address.'))
            ->action(Lang::get('Verify Email Address'), $verificationUrl)
            ->line(Lang::get('This verification link will expire in :count minutes.', ['count' => config('auth.passwords.users.expire', 60)]))
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    protected function verificationUrl($notifiable)
    {
        // dd('Verification Url');
        return route(Route::hasAdmin('complete.register.form'), [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);
    }

}
