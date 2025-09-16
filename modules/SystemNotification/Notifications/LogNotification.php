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

    protected array $logData;

    /**
     * Create a new notification instance.
     */
    public function __construct(LogRecord $record)
    {
        // Extract only serializable data from LogRecord
        $this->logData = [
            'level' => $record->level->name,
            'message' => $record->message,
            'context' => $this->sanitizeContext($record->context),
            'datetime' => $record->datetime->format('Y-m-d H:i:s'),
            'channel' => $record->channel,
        ];
    }

    /**
     * Remove non-serializable objects from context
     */
    private function sanitizeContext(array $context): array
    {
        return array_map(function ($value) {
            if (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    return (string) $value;
                } elseif (method_exists($value, 'toArray')) {
                    return $value->toArray();
                } else {
                    return get_class($value) . ' object';
                }
            }
            return $value;
        }, $context);
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting($this->logData['level'])
            ->subject('Modularity Log Alert: ' . $this->logData['level'])
            ->line('App URL: ' . config('app.url'))
            ->line('IP Address: ' . request()->ip())
            ->line('Time: ' . $this->logData['datetime'])
            ->line('Channel: ' . $this->logData['channel'])
            ->line(new HtmlString($this->logData['message']))
            ->line(new HtmlString('<pre>' . json_encode($this->logData['context'], JSON_PRETTY_PRINT) . '</pre>'))
            ->salutation(null);
    }
}
