<?php

namespace Unusualify\Modularity\Listeners;

use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Notification;
use Unusualify\Modularity\Facades\Modularity;

abstract class Listener
{
    protected $mailEnabled = false;
    /**
     * Notification paths
     */
    protected $notificationPaths = [];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        if (config('modularity.mail.enabled')) {
            $this->mailEnabled = true;
        }

        $this->addNotificationPath(Modularity::find('SystemNotification')->getDirectoryPath('Notifications'));
    }


    /**
     * Add notification path
     */
    public function addNotificationPath($path)
    {
        $this->notificationPaths[] = $path;
    }

    /**
     * Merge notification paths
     */
    public function mergeNotificationPaths($paths)
    {
        $this->notificationPaths = array_merge($this->notificationPaths, $paths);
    }

    /**
     * Get notification class based on event name
     */
    protected function getNotificationClass($event): ?string
    {
        $eventClass = get_class($event);
        $eventName = class_basename($eventClass);
        $notificationName = "{$eventName}Notification";

        foreach ($this->notificationPaths as $path) {

            if (!is_dir($path)) {
                continue;
            }

            // Find all PHP files in the directory
            $finder = new Finder();
            $finder->files()->in($path)->name('*.php');

            foreach ($finder as $file) {
                $className = $file->getBasename('.php');

                $className = get_file_class($file->getRealPath());
                if (get_class_short_name($className) === $notificationName) {
                    return $className;
                }
            }
        }

        return null;
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($this->mailEnabled) {
            $notificationClass = $this->getNotificationClass($event);

            if ($notificationClass) {
                Notification::route('mail', 'oguz.bukcuoglu@gmail.com')
                    ->notifyNow(new $notificationClass($event->model, $event->serializedData));
            }
        }


        // $event->model->notify(new ModelCreatedNotification());

        // Notification::route('mail', 'taylor@example.com')
        //     ->route('vonage', '5555555555')
        //     ->route('slack', '#slack-channel')
        //     ->route('broadcast', [new Channel('channel-name')])
        //     ->notify(new InvoicePaid($invoice));

        // Notification::route('mail', [
        //     'barrett@example.com' => 'Barrett Blair',
        // ])->notify(new InvoicePaid($invoice));

        // Notification::routes([
        //     'mail' => ['barrett@example.com' => 'Barrett Blair'],
        //     'vonage' => '5555555555',
        // ])->notify(new InvoicePaid($invoice));
    }
}
