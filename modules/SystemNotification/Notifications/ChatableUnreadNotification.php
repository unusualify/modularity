<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Unusualify\Modularity\Facades\ModularityLog;
use Modules\SystemNotification\Notifications\Contracts\AfterSendable;

class ChatableUnreadNotification extends FeatureNotification implements ShouldQueue, AfterSendable
{
    public function __construct(\Unusualify\Modularity\Entities\Chat $model)
    {
        parent::__construct($model->chatable);
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

    public function getNotificationSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = __(':moduleRouteHeadline', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = __('New Message on :moduleRouteHeadline', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = __('You have a new message.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$messageCallbacks[static::class]) && is_callable(static::$messageCallbacks[static::class])) {
            return call_user_func(static::$messageCallbacks[static::class], $notifiable, $model);
        }

        return $default;
    }

    public function afterNotificationSent($notifiable): void
    {
        try {
            $this->model->latestChatMessage()->first()->touchQuietly('notified_at');
        } catch (\Exception $e) {
            ModularityLog::error('Error updating notified_at for chatable model: ' . get_class($this->model), [
                'model' => $this->model,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
