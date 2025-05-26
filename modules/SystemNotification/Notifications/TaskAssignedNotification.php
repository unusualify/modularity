<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssignedNotification extends FeatureNotification implements ShouldQueue
{
    use Queueable;

    public function __construct($model)
    {
        // $model is a assignment model
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

    public function getModuleRouteHeadline(\Illuminate\Database\Eloquent\Model $model): string
    {
        return parent::getModuleRouteHeadline($model->assignable);
    }

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        return parent::getModelTitleField($model->assignable);
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('A Task Has Been Assigned To You');
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('The :moduleRouteHeadline :modelTitleField has been assigned to you as a task.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
            'modelTitleField' => "'{$this->getModelTitleField($model)}'",
        ]);
    }

    public function getNotificationRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        return parent::getNotificationRedirector($notifiable, $model->assignable);
    }
}
