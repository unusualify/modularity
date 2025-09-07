<?php

namespace Modules\SystemNotification\Notifications\Contracts;

interface AfterSendable
{
    public function afterNotificationSent($notifiable): void;
}
