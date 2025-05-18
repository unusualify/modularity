<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;

class StateableUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $model, public $newState, public $oldState) {}

    public function via($notifiable): array
    {
        $via = explode(',', config('modularity.notifications.stateable.channels', 'mail,database'));

        return $via;
    }

    public function getModuleRouteHeadline(\Illuminate\Database\Eloquent\Model $model): string
    {
        $moduleRouteModelName = get_class_short_name($model);

        return Str::headline($moduleRouteModelName);
    }

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        if (method_exists($model, 'getTitleValue')) {
            return $model->getTitleValue();
        } else {
            return $model->name
                ?? $model->title
                ?? $model->slug
                ?? $model->id;
        }
    }

    public function getStateableUrl(\Illuminate\Database\Eloquent\Model $model)
    {
        $moduleName = method_exists($model, 'moduleName') ? $model->moduleName() : null;
        $routeName = method_exists($model, 'routeName') ? $model->routeName() : null;

        if($moduleName && $routeName){
            $module = Modularity::find($moduleName);

            return $module->getRouteActionUrl(
                routeName: $routeName,
                action: 'index',
                replacements: [
                    'id' => $model->id
                ],
                absolute: true,
                isPanel: true
            );
        }

        return null;
    }

    public function getStateableMessage(\Illuminate\Database\Eloquent\Model $model): string
    {
        $moduleRouteHeadline = $this->getModuleRouteHeadline($model);
        $titleField = $this->getModelTitleField($model);

        return __('The status of the :moduleRouteHeadline :titleField has been changed to ', [
            'moduleRouteHeadline' => $moduleRouteHeadline,
            'titleField' => "'$titleField'",
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        $moduleRouteHeadline = $this->getModuleRouteHeadline($this->model);
        $url = $this->getStateableUrl($this->model);

        return (new MailMessage)
            ->subject($moduleRouteHeadline . ' Status Changed')
            // ->greeting(__('Hi, :name', ['name' => $notifiable->name]))
            // ->line($this->getStateableMessage($this->model))
            // ->action('View ' . $moduleRouteHeadline, $url)
            // ->line('Thank you for using our application!')
            // ->salutation(new HtmlString('Best Regards, <br>' . config('app.name')));
            ->markdown('modularity::mails.stateable', [
                'userName' => $notifiable->name,
                'message' => $this->getStateableMessage($this->model),
                'state' => $this->newState->name,
                'actionText' => 'View ' . $moduleRouteHeadline,
                'actionUrl' => $url,
                'level' => 'success',
                'displayableActionUrl' => $url,
            ]);
    }

    public function toArray($notifiable): array
    {
        return [

        ];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    // public function databaseType(object $notifiable): string
    // {
    //     return 'state-updated';
    // }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->getStateableMessage($this->model) . $this->newState->name,
            // 'model' => $this->model,
            // 'newState' => $this->newState,
            // 'oldState' => $this->oldState,
        ];
    }
}