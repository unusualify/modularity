<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
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

    public $validChannels = ['mail', 'database', 'broadcast', 'vonage', 'slack'];

    /**
     * The module route headline of the model.
     *
     * @var array<string, \Closure>
     */
    protected static array $moduleRouteHeadlineCallbacks = [];

    /**
     * The model title field of the model.
     *
     * @var array<string, \Closure>
     */
    protected static array $modelTitleFieldCallbacks = [];

    /**
     * The notification subject callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $subjectCallbacks = [];

    /**
     * The notification message callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $mailSubjectCallbacks = [];

    /**
     * The notification message callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $messageCallbacks = [];

    /**
     * The notification html message callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $htmlMessageCallbacks = [];

    /**
     * The notification mail message callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $mailMessageCallbacks = [];

    /**
     * The notification action text callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $actionTextCallbacks = [];

    /**
     * The notification mail action text callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $mailActionTextCallbacks = [];

    /**
     * The notification mail salutation callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $mailSalutationCallbacks = [];

    /**
     * The database feature fields callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $databaseFeatureFieldsCallbacks = [];

    /**
     * The mail message class callback of the notification.
     *
     * @var array<string, \Closure>
     */
    protected static array $mailMessageClassCallbacks = [];

    public function __construct(public \Illuminate\Database\Eloquent\Model $model)
    {
        $this->token = uniqid();
    }

    /**
     * Determine which connections should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return [
            'mail' => modularityConfig('notifications.mail_connection'),
            'database' => modularityConfig('notifications.database_connection'),
        ];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => modularityConfig('notifications.mail_queue'),
            'database' => modularityConfig('notifications.database_queue'),
        ];
    }

    public function getModel(): \Illuminate\Database\Eloquent\Model
    {
        return $this->model;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function isValidChannel(string $channel): bool
    {
        return in_array($channel, $this->validChannels);
    }

    public function getValidChannels($channels): array
    {
        if(!is_array($channels)){
            $channels = explode(',', $channels);
        }

        return array_filter($channels, function($channel) {
            return $this->isValidChannel($channel);
        });
    }

    /**
     * Set a callback that should be used when creating the module route headline.
     *
     * @param \Closure(\Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createModuleRouteHeadline(callable $callback)
    {
        static::$moduleRouteHeadlineCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the model title field.
     *
     * @param \Closure(\Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createModelTitleField(callable $callback)
    {
        static::$modelTitleFieldCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification subject.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createSubject(callable $callback)
    {
        static::$subjectCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification message.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createMailSubject(callable $callback)
    {
        static::$mailSubjectCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification message.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createMessage(callable $callback)
    {
        static::$messageCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification html message.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createHtmlMessage(callable $callback)
    {
        static::$htmlMessageCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification mail message.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createMailMessage(callable $callback)
    {
        static::$mailMessageCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification action text.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createActionText(callable $callback)
    {
        static::$actionTextCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification mail action text.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createMailActionText(callable $callback)
    {
        static::$mailActionTextCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the notification mail salutation.
     *
     * @param \Closure(mixed, \Illuminate\Database\Eloquent\Model): string $callback
     * @return void
     */
    public static function createMailSalutation(callable $callback)
    {
        static::$mailSalutationCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when creating the database feature fields.
     *
     * @param \Closure(array, mixed, \Illuminate\Database\Eloquent\Model): array $callback
     * @return void
     */
    public static function createDatabaseFeatureFields(callable $callback)
    {
        static::$databaseFeatureFieldsCallbacks[static::class] = $callback;
    }

    /**
     * Set a callback that should be used when updating the Laravel mail message.
     *
     * @param \Closure(MailMessage, mixed, \Illuminate\Database\Eloquent\Model): MailMessage $callback
     * @return void
     */
    public static function updateLaravelMailMessage(callable $callback)
    {
        static::$mailMessageClassCallbacks[static::class] = $callback;
    }

    /**
     * Get the module route headline of the model.
     */
    public function getModuleRouteHeadline(\Illuminate\Database\Eloquent\Model $model): string
    {
        if (isset(static::$moduleRouteHeadlineCallbacks[static::class]) && is_callable(static::$moduleRouteHeadlineCallbacks[static::class])) {
            return call_user_func(static::$moduleRouteHeadlineCallbacks[static::class], $model);
        }

        if (isset(static::$moduleRouteHeadlineCallbacks[self::class]) && is_callable(static::$moduleRouteHeadlineCallbacks[self::class])) {
            return call_user_func(static::$moduleRouteHeadlineCallbacks[self::class], $model);
        }

        $moduleRouteModelName = get_class_short_name($model);

        if (isset($model->notificationHeadline)) {
            return $model->notificationHeadline;
        }

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

        if (isset(static::$modelTitleFieldCallbacks[static::class]) && is_callable(static::$modelTitleFieldCallbacks[static::class])) {
            return call_user_func(static::$modelTitleFieldCallbacks[static::class], $model);
        }

        if (isset(static::$modelTitleFieldCallbacks[self::class]) && is_callable(static::$modelTitleFieldCallbacks[self::class])) {
            return call_user_func(static::$modelTitleFieldCallbacks[self::class], $model);
        }

        if (isset($model->notificationTitleField) && isset($model->{$model->notificationTitleField})) {
            return $model->{$model->notificationTitleField};
        }

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
     * Get the subject for the notification.
     */
    protected function getNotificationSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = $this->getModuleRouteHeadline($model);

        if (isset(static::$subjectCallbacks[static::class]) && is_callable(static::$subjectCallbacks[static::class])) {
            return call_user_func(static::$subjectCallbacks[static::class], $notifiable, $model, $default);
        }

        if (isset(static::$subjectCallbacks[self::class]) && is_callable(static::$subjectCallbacks[self::class])) {
            return call_user_func(static::$subjectCallbacks[self::class], $notifiable, $model, $default);
        }

        return $default;
    }

    /**
     * Get the subject for the notification.
     */
    protected function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = $this->getNotificationSubject($notifiable, $model);

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $model, $default);
        }

        if (isset(static::$mailSubjectCallbacks[self::class]) && is_callable(static::$mailSubjectCallbacks[self::class])) {
            return call_user_func(static::$mailSubjectCallbacks[self::class], $notifiable, $model, $default);
        }

        return $default;
    }

    /**
     * Get the message for the notification.
     */
    protected function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = __('The :moduleRouteHeadline :modelTitleField has been updated.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
            'modelTitleField' => "'{$this->getModelTitleField($model)}'",
        ]);

        if (isset(static::$messageCallbacks[static::class]) && is_callable(static::$messageCallbacks[static::class])) {
            return call_user_func(static::$messageCallbacks[static::class], $notifiable, $model, $default);
        }

        if (isset(static::$messageCallbacks[self::class]) && is_callable(static::$messageCallbacks[self::class])) {
            return call_user_func(static::$messageCallbacks[self::class], $notifiable, $model, $default);
        }

        return $default;
    }

    /**
     * Get the message for the notification.
     */
    protected function getNotificationMailMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = $this->getNotificationMessage($notifiable, $model);

        if (isset(static::$mailMessageCallbacks[static::class]) && is_callable(static::$mailMessageCallbacks[static::class])) {
            return call_user_func(static::$mailMessageCallbacks[static::class], $notifiable, $model, $default);
        }

        if (isset(static::$mailMessageCallbacks[self::class]) && is_callable(static::$mailMessageCallbacks[self::class])) {
            return call_user_func(static::$mailMessageCallbacks[self::class], $notifiable, $model, $default);
        }

        return $default;
    }

    /**
     * Get the html message for the notification.
     */
    protected function getNotificationHtmlMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = $this->getNotificationMessage($notifiable, $model);

        if (isset(static::$htmlMessageCallbacks[static::class]) && is_callable(static::$htmlMessageCallbacks[static::class])) {
            return call_user_func(static::$htmlMessageCallbacks[static::class], $notifiable, $model, $default);
        }

        if (isset(static::$htmlMessageCallbacks[self::class]) && is_callable(static::$htmlMessageCallbacks[self::class])) {
            return call_user_func(static::$htmlMessageCallbacks[self::class], $notifiable, $model, $default);
        }

        return $default;
    }

    /**
     * Get the action text for the notification.
     */
    public function getNotificationActionText(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = __('View :module', ['module' => $this->getModuleRouteHeadline($model) ?? __('Notification')]);

        if (isset(static::$actionTextCallbacks[static::class]) && is_callable(static::$actionTextCallbacks[static::class])) {
            return call_user_func(static::$actionTextCallbacks[static::class], $notifiable, $model, $default);
        }

        if (isset(static::$actionTextCallbacks[self::class]) && is_callable(static::$actionTextCallbacks[self::class])) {
            return call_user_func(static::$actionTextCallbacks[self::class], $notifiable, $model, $default);
        }

        return $default;
    }

    /**
     * Get the action text for the notification.
     */
    public function getNotificationMailActionText(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $default = $this->getNotificationActionText($notifiable, $model);

        if (isset(static::$mailActionTextCallbacks[static::class]) && is_callable(static::$mailActionTextCallbacks[static::class])) {
            return call_user_func(static::$mailActionTextCallbacks[static::class], $notifiable, $model, $default);
        }

        if (isset(static::$mailActionTextCallbacks[self::class]) && is_callable(static::$mailActionTextCallbacks[self::class])) {
            return call_user_func(static::$mailActionTextCallbacks[self::class], $notifiable, $model, $default);
        }

        return $default;
    }

    /**
     * Get the redirector to use in the notification system.
     *
     * @return string|null
     */
    protected function getNotificationRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        if (method_exists($this, 'redirectorModel')) {
            $model = $this->redirectorModel($model);
        }

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
    protected function getNotificationMailRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
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

    protected function getMailSalutation(): string
    {
        $default = $this->salutationMessage ?? new HtmlString('Best Regards,');

        if (isset(static::$mailSalutationCallbacks[static::class]) && is_callable(static::$mailSalutationCallbacks[static::class])) {
            return call_user_func(static::$mailSalutationCallbacks[static::class], $default);
        }

        if (isset(static::$mailSalutationCallbacks[self::class]) && is_callable(static::$mailSalutationCallbacks[self::class])) {
            return call_user_func(static::$mailSalutationCallbacks[self::class], $default);
        }

        return $default;
    }

    protected function getMailMessage(MailMessage $mailMessage, object $notifiable, \Illuminate\Database\Eloquent\Model $model): MailMessage
    {
        if (isset(static::$mailMessageClassCallbacks[static::class]) && is_callable(static::$mailMessageClassCallbacks[static::class])) {
            return call_user_func(static::$mailMessageClassCallbacks[static::class], $mailMessage, $notifiable, $model);
        }

        if (isset(static::$mailMessageClassCallbacks[self::class]) && is_callable(static::$mailMessageClassCallbacks[self::class])) {
            return call_user_func(static::$mailMessageClassCallbacks[self::class], $mailMessage, $notifiable, $model);
        }

        return $mailMessage;
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
            'htmlMessage' => $this->getNotificationHtmlMessage($notifiable, $this->model),
            'redirectorText' => $actionText,
            'redirector' => $redirector,
            'hasRedirector' => $hasRedirector,
        ];
    }

    /**
     * Get the database feature fields for the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $fields = $this->toDatabaseFeatureFields($notifiable);

        $callback = null;
        if (isset(static::$databaseFeatureFieldsCallbacks[static::class]) && is_callable(static::$databaseFeatureFieldsCallbacks[static::class])) {
            $callback = static::$databaseFeatureFieldsCallbacks[static::class];
        } elseif (isset(static::$databaseFeatureFieldsCallbacks[self::class]) && is_callable(static::$databaseFeatureFieldsCallbacks[self::class])) {
            $callback = static::$databaseFeatureFieldsCallbacks[self::class];
        }

        if ($callback) {
            $fields = array_merge($fields, call_user_func($callback, $fields, $notifiable, $this->model), Arr::only($fields, ['token']));
        }

        return $fields;
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
        $mailRedirector = $this->getNotificationMailRedirector($notifiable, $this->model);
        $mailSubject = $this->getNotificationMailSubject($notifiable, $this->model);

        $redirector = $mailRedirector ?? $redirector ?? null;

        $message = $this->getNotificationMailMessage($notifiable, $this->model);

        $mailMessage = (new MailMessage)
            ->subject($mailSubject)
            ->greeting(__('Hi, :name', ['name' => $notifiable->name]))
            ->line($message);

        if ($redirector) {
            $mailMessage->action($this->getNotificationMailActionText($notifiable, $this->model), $redirector);
        }

        $mailMessage = $mailMessage->salutation($this->getMailSalutation());

        return $this->getMailMessage($mailMessage, $notifiable, $this->model);
    }
}
