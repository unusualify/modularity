<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Unusualify\Modularity\Facades\ModularityLog;

class ChatableUnreadNotification extends FeatureNotification implements ShouldQueue
{
    public function __construct($model)
    {
        parent::__construct($model);
    }

    public function via($notifiable): array
    {
        $via = explode(',', config('modularity.notifications.chatable.channels', 'database,mail'));

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

    public function getNotificationSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('Press Release', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('New Message on :moduleRouteHeadline', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('You have a new message.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
            'modelTitleField' => $this->getModelTitleField($model) ?? __('the'),
        ]);
    }

    public function afterNotificationSent(): void
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
