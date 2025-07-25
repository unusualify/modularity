<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentFailedNotification extends FeatureNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param \Modules\SystemPayment\Entities\Payment $model
     * @return void
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    public function via($notifiable): array
    {
        $via = explode(',', config('modularity.notifications.payment.channels', 'mail,database'));

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

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        $priceableModel = $model->price && $model->price->priceable ? $model->price->priceable : $model;

        $default = parent::getModelTitleField($priceableModel);

        if (isset(static::$modelTitleFieldCallbacks[static::class]) && is_callable(static::$modelTitleFieldCallbacks[static::class])) {
            return call_user_func(static::$modelTitleFieldCallbacks[static::class], $priceableModel, $default);
        }

        return $default;
    }

    public function getModuleRouteHeadline(\Illuminate\Database\Eloquent\Model $model): string
    {
        $priceableModel = $model->price && $model->price->priceable ? $model->price->priceable : $model;

        $default = parent::getModuleRouteHeadline($priceableModel);

        if (isset(static::$moduleRouteHeadlineCallbacks[static::class]) && is_callable(static::$moduleRouteHeadlineCallbacks[static::class])) {
            return call_user_func(static::$moduleRouteHeadlineCallbacks[static::class], $priceableModel, $default);
        }

        return $default;
    }

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $priceableModel = $model->price && $model->price->priceable ? $model->price->priceable : $model;

        $default = __('Payment Failed');

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $priceableModel, $default);
        }

        return $default;
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        $priceableModel = $model->price && $model->price->priceable ? $model->price->priceable : $model;

        $default = __('The :moduleRouteHeadline :modelTitleField\'s payment has been failed.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($priceableModel),
            'modelTitleField' => "'{$this->getModelTitleField($priceableModel)}'",
        ]);

        if (isset(static::$messageCallbacks[static::class]) && is_callable(static::$messageCallbacks[static::class])) {
            return call_user_func(static::$messageCallbacks[static::class], $notifiable, $priceableModel, $default);
        }

        return $default;
    }

    public function getNotificationRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        $priceableModel = $model->price && $model->price->priceable ? $model->price->priceable : $model;

        return parent::getNotificationRedirector($notifiable, $priceableModel);
    }

    public function getMailMessage(MailMessage $mailMessage, object $notifiable, \Illuminate\Database\Eloquent\Model $model): MailMessage
    {
        $mailMessage = $mailMessage
            ->line('Price: ' . $model->amount_formatted)
            ->line('Payment ID: ' . $model->id)
            ->line('Payment Order ID: ' . $model->order_id)
            ->line('Payment Service: ' . ($model->paymentService ? $model->paymentService->name : $model->payment_gateway))
            ->line('Payment Request: ' . json_encode($model->parameters, JSON_PRETTY_PRINT))
            ->line('Payment Response: ' . json_encode($model->response, JSON_PRETTY_PRINT));

        if (isset(static::$mailMessageClassCallbacks[static::class]) && is_callable(static::$mailMessageClassCallbacks[static::class])) {
            return call_user_func(static::$mailMessageClassCallbacks[static::class], $mailMessage, $notifiable, $model);
        }

        return $mailMessage;
    }
}
