<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Monolog\LogRecord;

class LogNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected LogRecord $record)
    {

    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting($this->record->level->name)
            ->subject('Modularity Log Alert: ' . $this->record->level->name)
            ->line('App URL: ' . config('app.url'))
            ->line('IP Address: ' . request()->ip())
            ->line(new HtmlString($this->record->message))
            ->line(new HtmlString(json_encode($this->record->context, JSON_PRETTY_PRINT)))
            ->salutation(null);
    }
}