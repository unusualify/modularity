<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;

class LoginMfaCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public Carbon $expiresAt,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Login Verification Code'))
            ->line(Lang::get('Use this code to complete your login: :code', ['code' => $this->code]))
            ->line(Lang::get('This code will expire at :time', ['time' => $this->expiresAt->format('H:i')]))
            ->line(Lang::get('If you did not attempt to login, you can ignore this email.'));
    }
}
