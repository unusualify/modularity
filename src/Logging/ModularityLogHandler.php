<?php

namespace Unusualify\Modularity\Logging;

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ModularityLogHandler extends AbstractProcessingHandler
{
    protected $logPath;

    public function __construct($level = Level::Debug)
    {
        parent::__construct($level);
        $this->logPath = storage_path('logs' .  '/' . env('MODULARITY_LOG_FILE', 'modularity.log'));
    }

    protected function write(LogRecord $record): void
    {

        // Handle emergency, alert, and critical levels with email
        if ($record->level->value >= Level::Critical->value) {
            $this->sendEmailNotification($record);
        }

        // Write to custom log file for debug and above
        if ($record->level->value >= Level::Debug->value) {
            $this->writeToFile($record);
        }
    }

    protected function writeToFile(LogRecord $record): void
    {
        $formattedMessage = sprintf(
            "[%s] %s.%s: %s\n",
            $record->datetime->format('Y-m-d H:i:s'),
            $record->level->name,
            $record->channel,
            $record->message
        );

        if (!empty($record->context)) {
            $formattedMessage .= "Context: " . json_encode($record->context, JSON_PRETTY_PRINT) . "\n";
        }

        file_put_contents(
            $this->logPath,
            $formattedMessage,
            FILE_APPEND | LOCK_EX
        );
    }

    protected function sendEmailNotification(LogRecord $record): void
    {
        // You can implement your email notification logic here
        // For example, using Laravel's Mail facade:
        Notification::route('mail', 'oguzhan@unusualgrowth.com')
            ->notify(new \Modules\SystemNotification\Notifications\LogNotification($record));
    }
}