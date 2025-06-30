<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;

abstract class FeatureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The token of the notification.
     *
     * @var string
     */
    protected $token;

    /**
     * The headline of the model.
     *
     * @var string
     */
    public $modelHeadline;

    /**
     * The title field of the model.
     *
     * @var string
     */
    public $modelTitleField;

    /**
     * The salutation message of the notification.
     *
     * @var string
     */
    public $salutationMessage;

    public function __construct(public \Illuminate\Database\Eloquent\Model $model)
    {
        $this->token = uniqid();
    }

    public function getModel(): \Illuminate\Database\Eloquent\Model
    {
        return $this->model;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get the module route headline of the model.
     */
    public function getModuleRouteHeadline(\Illuminate\Database\Eloquent\Model $model): string
    {
        $moduleRouteModelName = get_class_short_name($model);

        return is_null($this->modelHeadline) ? Str::headline($moduleRouteModelName) : $this->modelHeadline;
    }

    /**
     * Get the title field of the model.
     */
    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        // if(!is_null($this->modelTitleField)){
        //     return $this->model->{$this->modelTitleField};
        // }

        if (method_exists($model, 'getTitleValue')) {
            return $model->getTitleValue() ?? '';
        } else {
            return $model->name
                ?? $model->title
                ?? $model->slug
                ?? $model->id
                ?? '';
        }
    }

    /**
     * Get the database feature fields for the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toDatabaseFeatureFields($notifiable);
    }

    /**
     * Get the database feature fields for the notification.
     */
    public function toDatabaseFeatureFields(object $notifiable): array
    {
        $message = $this->getNotificationMessage($notifiable, $this->model);
        $actionText = $this->getNotificationActionText($notifiable, $this->model);

        $redirector = $this->getNotificationRedirector($notifiable, $this->model);
        $hasRedirector = $redirector ? true : false;

        return [
            'token' => $this->token,
            'subject' => $this->getNotificationSubject($notifiable, $this->model),
            'message' => $message,
            'htmlMessage' => method_exists($this, 'getNotificationHtmlMessage')
                ? $this->getNotificationHtmlMessage($notifiable, $this->model)
                : $message,
            'redirectorText' => $actionText,
            'redirector' => $redirector,
            'hasRedirector' => $hasRedirector,
        ];
    }

    /**
     * Get the mail message for the notification.
     *
     * @param object $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $moduleRouteHeadline = $this->getModuleRouteHeadline($this->model);
        $redirector = $this->getNotificationRedirector($notifiable, $this->model);

        // Get the latest notification record from database
        $mailRedirector = method_exists($this, 'getNotificationMailRedirector')
            ? $this->getNotificationMailRedirector($notifiable, $this->model)
            : null;

        $mailSubject = method_exists($this, 'getNotificationMailSubject')
            ? $this->getNotificationMailSubject($notifiable, $this->model)
            : $moduleRouteHeadline . ' Changed';

        $redirector = $mailRedirector ?? $redirector ?? null;

        $message = method_exists($this, 'getNotificationMailMessage')
            ? $this->getNotificationMailMessage($notifiable, $this->model)
            : $this->getNotificationMessage($notifiable, $this->model);

        $mailMessage = (new MailMessage)
            ->subject($mailSubject)
            ->greeting(__('Hi, :name', ['name' => $notifiable->name]))
            ->line($message);

        if ($redirector) {
            $mailMessage->action('View ' . $moduleRouteHeadline, $redirector);
        }

        $mailMessage = $mailMessage->salutation(! is_null($this->salutationMessage) ? $this->salutationMessage : new HtmlString('Best Regards, <br>' . config('app.name')));

        if (method_exists($this, 'getMailMessage')) {
            $mailMessage = $this->getMailMessage($notifiable, $mailMessage, $this->model);
        }

        return $mailMessage;
    }

    /**
     * Get the subject for the notification.
     */
    public function getNotificationSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return $this->getModuleRouteHeadline($model);
    }

    /**
     * Get the message for the notification.
     */
    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('The :moduleRouteHeadline :modelTitleField has been updated.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
            'modelTitleField' => "'{$this->getModelTitleField($model)}'",
        ]);
    }

    /**
     * Get the action text for the notification.
     */
    public function getNotificationActionText(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('View :module', ['module' => $this->getModuleRouteHeadline($model) ?? __('Notification')]);
    }

    /**
     * Get the redirector to use in the notification system.
     *
     * @return string|null
     */
    public function getNotificationRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        $moduleName = method_exists($model, 'moduleName') ? $model->moduleName() : null;
        $routeName = method_exists($model, 'routeName') ? $model->routeName() : null;

        if ($moduleName && $routeName) {
            $module = Modularity::find($moduleName);

            $routeConfig = $module->getRouteConfig($routeName);

            $defaultEditOnModal = config('modularity.default_table_attributes.editOnModal', true);

            $editOnModal = data_get($routeConfig, 'table_options.editOnModal', $defaultEditOnModal);

            if ($editOnModal) {
                return $module->getRouteActionUrl(
                    routeName: $routeName,
                    action: 'index',
                    replacements: [
                        'id' => $model->id,
                    ],
                    absolute: true,
                    isPanel: true
                );
            } else {
                return $module->getRouteActionUrl(
                    routeName: $routeName,
                    action: 'edit',
                    replacements: [
                        Str::snake($routeName) => $model->id,
                    ],
                    absolute: true,
                    isPanel: true
                );
            }
        }

        return null;
    }

    /**
     * Get the redirector for the email action button.
     *
     * @return string|null
     */
    public function getNotificationMailRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        $notificationRecord = $notifiable->notifications()
            ->where('type', get_class($this))
            ->where('data->token', $this->token)
            ->latest()
            ->first();

        $mailRedirector = null;

        if ($notificationRecord) {
            $mailRedirector = route('admin.system.system_notification.my_notification.show', [
                'my_notification' => $notificationRecord->id,
                'redirector' => true,
            ]);
        }

        return $mailRedirector;
    }
}
