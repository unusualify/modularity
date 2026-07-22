<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Entities\Chat;

/**
 * Immediate retainable toast when a chat message is created.
 *
 * Separate from {@see ChatableUnreadNotification} (scheduler interval path):
 * this must not stamp `notified_at` / implement AfterSendable.
 */
class ChatableMessageBroadcastNotification extends FeatureNotification implements ShouldQueue
{
    /**
     * Fallback when config channels are unset or blank (empty env override).
     *
     * @var array<string>
     */
    public $defaultChannels = ['database', 'broadcast'];

    /**
     * The chat that received the message (parent model is the chatable).
     */
    protected Chat $chat;

    public function __construct(Chat $model)
    {
        $this->chat = $model;

        parent::__construct($model->chatable);
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }

    /**
     * Immediate message alerts stay in the retainable tray until closed or TTL.
     */
    public function isRetainable(): bool
    {
        return true;
    }

    /**
     * One tray slot per chat — later messages upsert the same retainable item.
     */
    public function getRetainGroup(): ?string
    {
        return 'chat:'.$this->chat->getKey();
    }

    public function toArray($notifiable): array
    {
        return [];
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

    public function getNotificationActionText(object $notifiable, Model $model): string
    {
        $default = __('Look');

        if (isset(static::$actionTextCallbacks[static::class]) && is_callable(static::$actionTextCallbacks[static::class])) {
            return call_user_func(static::$actionTextCallbacks[static::class], $notifiable, $model, $default);
        }

        return $default;
    }
}
