<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ModelCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $model)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
        // return $notifiable->prefers_sms ? ['vonage'] : ['mail', 'database'];

    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $moduleRouteModelName = get_class_short_name($this->model);
        $moduleRouteHeadline = Str::headline($moduleRouteModelName);

        if(method_exists($this->model, 'getTitleValue')) {
            $titleField = $this->model->getTitleValue();
        }else{
            $titleField = $this->model->name
                ?? $this->model->title
                ?? $this->model->slug
                ?? $this->model->id;
        }

        return (new MailMessage)
            // ->from('barrett@example.com', 'Barrett Blair')
            // ->error()
            // ->theme('light')
            ->greeting(__('Hi,'))
            ->subject('New ' . $moduleRouteHeadline)
            ->line("A new {$moduleRouteHeadline} was created called '{$titleField}'.")
            // ->lineIf($this->amount > 0, "Amount paid: {$this->amount}")
            // ->action('Notification Action', 'https://laravel.com')
            ->line('Thank you for using our application!')
            // ->lines([
            //     'A new ' . $moduleRouteHeadline . ' was created called ' . $titleField . '.',
            //     'Thank you for using our application!',
            // ])
            // ->view(
            //     'mail.invoice.paid', ['invoice' => $this->invoice]
            // )
            // ->view(
            //     ['mail.invoice.paid', 'mail.invoice.paid-text'],
            //     ['invoice' => $this->invoice]
            // )
            // ->text(
            //     'mail.invoice.paid-text', ['invoice' => $this->invoice]
            // )
            // ->attach('/path/to/file')
            // ->attach('/path/to/file', [
            //     'as' => 'name.pdf',
            //     'mime' => 'application/pdf',
            // ])
            // ->attachFromStorage('/path/to/file')
            // ->attachMany([
            //     '/path/to/forge.svg',
            //     '/path/to/vapor.svg' => [
            //         'as' => 'Logo.svg',
            //         'mime' => 'image/svg+xml',
            //     ],
            // ])
            // ->attachData($this->pdf, 'name.pdf', [ // pdf is a raw string of bytes
            //     'mime' => 'application/pdf',
            // ])
            // ->tag('upvote')
            // ->metadata('comment_id', $this->comment->id)
            // ->withSymfonyMessage(function (Symfony\Component\Mime\Email $message) {
            //     $message->getHeaders()->addTextHeader(
            //         'Custom-Header', 'Header Value'
            //     );
            // })
            // ->markdown('mail.invoice.paid', ['url' => $url])
            ->salutation(new HtmlString('Best Regards, <br>' . config('app.name')));

        // return (new InvoicePaidMailable($this->invoice))
        //     ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [];
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }
}
