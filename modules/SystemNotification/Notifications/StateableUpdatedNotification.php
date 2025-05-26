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

        return $via;
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

        return __('The status of the :moduleRouteHeadline :titleField has been changed to ', [
            'moduleRouteHeadline' => $moduleRouteHeadline,
            'titleField' => "'$titleField'",
        ]);
    }

    public function getNotificationHtmlMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return $this->getNotificationMessage($notifiable, $model) . $model->state_formatted;
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __(':moduleRouteHeadline Status Changed', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);
    }

    public function getMailMessage(object $notifiable, MailMessage $mailMessage, \Illuminate\Database\Eloquent\Model $model): MailMessage
    {
        $redirector = $this->getNotificationRedirector($notifiable, $this->getModel());

        // Get the latest notification record from database
        $mailRedirector = $this->getNotificationMailRedirector($notifiable, $this->getModel());

        // dd($mailRedirector, $redirector, $this->getToken());

        // return (new MailMessage)
        //     ->subject($moduleRouteHeadline . ' Status Changed'))
        return $mailMessage
            ->markdown('modularity::mails.stateable', [
                'userName' => $notifiable->name,
                'message' => $this->getNotificationMessage($notifiable, $this->getModel()),
                'state' => $this->newState->name,
                'actionText' => $this->getNotificationActionText($notifiable, $this->getModel()),
                'actionUrl' => $mailRedirector ?? $redirector,
                'level' => 'success',
                'displayableActionUrl' => $mailRedirector ?? $redirector,
            ]);
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
