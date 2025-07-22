<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssignedNotification extends FeatureNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param \Modules\SystemTask\Entities\Assignment $model
     * @return void
     */
    public function __construct(\Unusualify\Modularity\Entities\Assignment $model)
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
        $assignable = $model->assignable;

        $default = parent::getModuleRouteHeadline($assignable);

        if (isset(static::$moduleRouteHeadlineCallbacks[static::class]) && is_callable(static::$moduleRouteHeadlineCallbacks[static::class])) {
            return call_user_func(static::$moduleRouteHeadlineCallbacks[static::class], $assignable, $default);
        }

        return $default;
    }

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        $assignable = $model->assignable;

        $default = parent::getModelTitleField($assignable);

        if (isset(static::$modelTitleFieldCallbacks[static::class]) && is_callable(static::$modelTitleFieldCallbacks[static::class])) {
            return call_user_func(static::$modelTitleFieldCallbacks[static::class], $assignable, $default);
        }

        return $default;
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $assignable = $model->assignable;

        $default = __('A Task Has Been Assigned To You');

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $assignable, $default);
        }

        return $default;
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $assignable = $model->assignable;

        $default = __('The :moduleRouteHeadline :modelTitleField has been assigned to you as a task.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
            'modelTitleField' => "'{$this->getModelTitleField($model)}'",
        ]);

        if (isset(static::$messageCallbacks[static::class]) && is_callable(static::$messageCallbacks[static::class])) {
            return call_user_func(static::$messageCallbacks[static::class], $notifiable, $assignable, $default);
        }

        return $default;
    }

    public function getNotificationRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        return parent::getNotificationRedirector($notifiable, $model->assignable);
    }
}
