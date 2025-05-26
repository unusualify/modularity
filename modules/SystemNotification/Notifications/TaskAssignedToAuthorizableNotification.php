<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssignedToAuthorizableNotification extends FeatureNotification implements ShouldQueue
{
    use Queueable;

    public function __construct($model)
    {
        // model is a assignable model
        parent::__construct($model);
    }

    public function via($notifiable): array
    {
        $via = explode(',', config('modularity.notifications.authorizable.channels', 'database,mail'));

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
        return __('A Task Created On The :moduleRouteHeadline', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);
    }

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        return parent::getModelTitleField($model);
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('A task has been created in the \':moduleRouteHeadline\' which you are authorised to.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);
    }
}