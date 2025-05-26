<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskUpdatedNotification extends FeatureNotification implements ShouldQueue
{
    use Queueable;

    public function __construct($model)
    {
        parent::__construct($model);
    }

    public function via($notifiable): array
    {
        $via = explode(',', config('modularity.notifications.assignable.channels', 'mail,database'));

        return $via;
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }

    public function toArray($notifiable): array
    {
        return [

        ];
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('The Task Updated');
    }

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        return parent::getModelTitleField($model->assignable);
    }

    public function getNotificationRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        return parent::getNotificationRedirector($notifiable, $model->assignable);
    }

    public function getMailMessage(object $notifiable, MailMessage $mailMessage, \Illuminate\Database\Eloquent\Model $model): MailMessage
    {
        return $mailMessage->line('Status: ' . $model->status_label);
    }
}
