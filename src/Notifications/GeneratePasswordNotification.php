<?php

namespace Unusualify\Modularity\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class GeneratePasswordNotification extends Notification
{
    /**
     * The token of the password reset.
     *
     * @var string
     */
    public $token;

    /**
     * The callback that should be used to create the verify email URL.
     *
     * @var \Closure|null
     */
    public static $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Create a notification instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param mixed $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $generatePasswordUrl = $this->generatePasswordUrl($notifiable);

        return $this->buildMailMessage($generatePasswordUrl);
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param string $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(Lang::get('Generate Your Password For New Account'))
            ->line(Lang::get('Welcome to ' . env('APP_NAME') . '. Please click the button below to generate your password.'))
            ->action(Lang::get('Generate Password'), $url);
        // ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function generatePasswordUrl($notifiable)
    {
        return url(route('admin.register.password.generate.form', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordGeneration(),
        ], false));

        return URL::temporarySignedRoute(
            Route::hasAdmin('register.password.reset.form', ['token' => $this->token]),
            // Carbon::now()->addMinutes(Config::get('auth.verification.expire', 1440)),
            Carbon::now()->addHours(1),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForPasswordGeneration()),
            ]
        );
    }

    /**
     * Set a callback that should be used when creating the email verification URL.
     *
     * @param \Closure $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param \Closure $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
