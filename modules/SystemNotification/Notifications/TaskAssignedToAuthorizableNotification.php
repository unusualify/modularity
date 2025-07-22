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
        $default = __('A Task Created On The :moduleRouteHeadline', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        $default = parent::getModelTitleField($model);

        if (isset(static::$modelTitleFieldCallbacks[static::class]) && is_callable(static::$modelTitleFieldCallbacks[static::class])) {
            return call_user_func(static::$modelTitleFieldCallbacks[static::class], $model, $default);
        }

        return $default;
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = __('A task has been created in the \':moduleRouteHeadline\' which you are authorised to.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$messageCallbacks[static::class]) && is_callable(static::$messageCallbacks[static::class])) {
            return call_user_func(static::$messageCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }
}
