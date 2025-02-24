<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ModelDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $modelData;

    public function __construct(public $model, $modelData)
    {
        $this->modelData = $modelData;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $moduleRouteModelName = get_class_short_name($this->model);
        $moduleRouteHeadline = Str::headline($moduleRouteModelName);

        $titleValue = method_exists($this->model, 'getRouteTitleColumnKey')
            ? $this->modelData[$this->model->getRouteTitleColumnKey()]
            : $this->modelData['name']
                ?? $this->modelData['title']
                ?? $this->modelData['slug']
                ?? $this->modelData['id'];

        return (new MailMessage)
            ->greeting(__('Hi,'))
            ->subject($moduleRouteHeadline . ' Deleted')
            ->line("The {$moduleRouteHeadline} '{$titleValue}' has been deleted.")
            ->line(new HtmlString($this->formatModelDetails()))
            ->line('Thank you for using our application!')
            ->salutation(new HtmlString('Best Regards, <br>' . config('app.name')));
    }

    public function toArray($notifiable): array
    {
        return [];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }

    protected function formatModelDetails(): string
    {
        $details = '';
        foreach ($this->modelData as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }
            $details .= "<strong>{$key}</strong>: {$value}<br>";
        }

        return $details;
    }
}
