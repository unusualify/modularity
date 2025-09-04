<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StateableUpdatedNotification extends FeatureNotification implements ShouldQueue
{
    use Queueable;

    public function __construct($model, public $newState, public $oldState)
    {
        parent::__construct($model);

    }

    public function via($notifiable): array
    {
        $via = explode(',', config('modularity.notifications.stateable.channels', 'database,mail'));
        // clean values not being 'mail' 'database' 'broadcast' 'vonage' 'slack'
        return $this->getValidChannels($via);
    }

    public function toArray($notifiable): array
    {
        return [];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $moduleRouteHeadline = $this->getModuleRouteHeadline($model);
        $titleField = $this->getModelTitleField($model);

        $default = __('The status of the :moduleRouteHeadline :titleField has been changed to ', [
            'moduleRouteHeadline' => $moduleRouteHeadline,
            'titleField' => "'$titleField'",
        ]);

        if (isset(static::$messageCallbacks[static::class]) && is_callable(static::$messageCallbacks[static::class])) {
            return call_user_func(static::$messageCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getNotificationHtmlMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = $this->getNotificationMessage($notifiable, $model) . $model->state_formatted;

        if (isset(static::$htmlMessageCallbacks[static::class]) && is_callable(static::$htmlMessageCallbacks[static::class])) {
            return call_user_func(static::$htmlMessageCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = __(':moduleRouteHeadline Status Changed', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getMailMessage(MailMessage $mailMessage, object $notifiable, \Illuminate\Database\Eloquent\Model $model): MailMessage
    {
        $redirector = $this->getNotificationRedirector($notifiable, $this->getModel());

        // Get the latest notification record from database
        $mailRedirector = $this->getNotificationMailRedirector($notifiable, $this->getModel());

        $mailMessage = $mailMessage
            ->markdown('modularity::mails.stateable', [
                'userName' => $notifiable->name,
                'message' => $this->getNotificationMessage($notifiable, $this->getModel()),
                'state' => $this->newState->name,
                'actionText' => $this->getNotificationActionText($notifiable, $this->getModel()),
                'actionUrl' => $mailRedirector ?? $redirector,
                'level' => 'success',
                'displayableActionUrl' => $mailRedirector ?? $redirector,
                'salutation' => $this->getMailSalutation(),
            ]);

        if (isset(static::$mailMessageClassCallbacks[static::class]) && is_callable(static::$mailMessageClassCallbacks[static::class])) {
            return call_user_func(static::$mailMessageClassCallbacks[static::class], $mailMessage, $notifiable, $this->model);
        }

        return $mailMessage;
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    // public function databaseType(object $notifiable): string
    // {
    //     return 'state-updated';
    // }
}
