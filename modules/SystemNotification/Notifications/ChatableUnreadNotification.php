<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Modules\SystemNotification\Notifications\Contracts\AfterSendable;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Facades\ModularousLog;

class ChatableUnreadNotification extends FeatureNotification implements AfterSendable, ShouldQueue
{
    /**
     * Fallback when config channels are unset or blank (empty env override).
     *
     * @var array<string>
     */
    public $defaultChannels = ['database', 'mail', 'broadcast'];

    /**
     * Guard so multi-channel NotificationSent hooks only stamp once.
     */
    protected bool $notifiedAtStamped = false;

    public function __construct(Chat $model)
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

    public function getNotificationSubject(object $notifiable, Model $model): string
    {
        $default = __(':moduleRouteHeadline', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getNotificationMailSubject(object $notifiable, Model $model): string
    {
        $default = __('New Message on :moduleRouteHeadline', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
        ]);

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }

    public function getNotificationMessage(object $notifiable, Model $model): string
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
        // Idempotent: handleChatableNotification also stamps notified_at after notifyNow.
        // Keep this as a safety net when the notification is sent outside the scheduler.
        if ($this->notifiedAtStamped) {
            return;
        }

        try {
            $message = $this->model->latestChatMessage()->first();
            if (! $message || $message->notified_at) {
                $this->notifiedAtStamped = true;

                return;
            }

            $message->forceFill(['notified_at' => now()])->saveQuietly();
            $this->notifiedAtStamped = true;
        } catch (\Throwable $e) {
            ModularousLog::error('Error updating notified_at for chatable model: ' . get_class($this->model), [
                'model' => $this->model,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
