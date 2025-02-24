<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ModelUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $model)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $moduleRouteModelName = get_class_short_name($this->model);
        $moduleRouteHeadline = Str::headline($moduleRouteModelName);

        // dd(
        //     // $this->model->getDirty(),
        //     // $this->model->getOriginal(),
        //     // $this->model->getAttributes(),
        //     $this->model->getChanges(),
        //     // $this->model->getChangesToBeSaved(),
        //     // $this->model->getChangesToBeSaved(),
        //     // $this->model->getChangesToBeSaved(),
        //     // $this->model->getChangesToBeSaved(),
        // );

        if(method_exists($this->model, 'getTitleValue')) {
            $titleField = $this->model->getTitleValue();
        }else{
            $titleField = $this->model->name
                ?? $this->model->title
                ?? $this->model->slug
                ?? $this->model->id;
        }

        return (new MailMessage)
            ->greeting(__('Hi,'))
            ->subject($moduleRouteHeadline . ' Updated')
            ->line("The {$moduleRouteHeadline} '{$titleField}' has been updated.")
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
}
