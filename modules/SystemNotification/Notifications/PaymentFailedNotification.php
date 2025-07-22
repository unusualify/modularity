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

    public function getNotificationMailSubject(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('Payment Failed');
    }

    public function getModelTitleField(\Illuminate\Database\Eloquent\Model $model): string
    {
        return parent::getModelTitleField($model->price && $model->price->priceable ? $model->price->priceable : $model);
    }

    public function getModuleRouteHeadline(\Illuminate\Database\Eloquent\Model $model): string
    {
        return parent::getModuleRouteHeadline($model->price && $model->price->priceable ? $model->price->priceable : $model);
    }

    public function getNotificationMessage(object $notifiable, \Illuminate\Database\Eloquent\Model $model): string
    {
        return __('The :moduleRouteHeadline :modelTitleField\'s payment has been failed.', [
            'moduleRouteHeadline' => $this->getModuleRouteHeadline($model),
            'modelTitleField' => "'{$this->getModelTitleField($model)}'",
        ]);
    }

    public function getNotificationRedirector(object $notifiable, \Illuminate\Database\Eloquent\Model $model)
    {
        return parent::getNotificationRedirector($notifiable, $model->price && $model->price->priceable ? $model->price->priceable : $model);
    }

    public function getMailMessage(object $notifiable, MailMessage $mailMessage, \Illuminate\Database\Eloquent\Model $model): MailMessage
    {
        return $mailMessage
            ->line('Price: ' . $model->amount_formatted)
            ->line('Payment ID: ' . $model->id)
            ->line('Payment Order ID: ' . $model->order_id)
            ->line('Payment Service: ' . ($model->paymentService ? $model->paymentService->name : $model->payment_gateway))
            ->line('Payment Request: ' . json_encode($model->parameters, JSON_PRETTY_PRINT))
            ->line('Payment Response: ' . json_encode($model->response, JSON_PRETTY_PRINT));
    }
}
