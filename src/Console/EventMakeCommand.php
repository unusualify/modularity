<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularity\Facades\Modularity;
use function Laravel\Prompts\{select, text};

class EventMakeCommand extends BaseCommand
{
    protected $hidden = true;

    	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'modularity:make:event
		{name : The name of event}
		{module? : The name of module}
        {--f|force : Overwrite existing file}
        {--should-broadcast : Should the event broadcast}
        {--should-broadcast-now : Should the event broadcast now}
        {--should-dispatch-after-commit : Should the event dispatch after commit}';


    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Laravel Event';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function _handle(): int
    {
        $name = $this->argument('name');
        $module = $this->argument('module');
        // handle command
        $this->info('Event created');
        return 0;
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $moduleName = $this->getModuleName();
        $className = $this->getFileName();

        $namespace = 'App\Events';

        if($moduleName) {
            $module = Modularity::findOrFail($moduleName);
            $namespace = $module->getTargetClassNamespace('event');
        }

        $implements = [];
        $namespaces = [];
        $attributes = [];
        $methods = [];

        if($this->option('should-dispatch-after-commit')) {
            $implements[] = 'ShouldDispatchAfterCommit';
            $namespaces[] = 'Illuminate\Contracts\Events\ShouldDispatchAfterCommit';
        }

        if($this->option('should-broadcast-now')) {
            $implements[] = 'ShouldBroadcastNow';
            $namespaces[] = 'Illuminate\Contracts\Broadcasting\ShouldBroadcastNow';

        } else if($this->option('should-broadcast')) {
            $implements[] = 'ShouldBroadcast';
            $namespaces[] = 'Illuminate\Contracts\Broadcasting\ShouldBroadcast';

            $attributes[] = attribute_string(
                attribute_name: 'connection',
                value: select(
                    label: 'Select the queue connection?',
                    options: array_keys(config('queue.connections')),
                    default: 'redis',
                ),
                comment: 'The name of the queue connection to use when broadcasting the event.',
            );
            $attributes[] = attribute_string(
                attribute_name: 'queue',
                value: text(
                    label: 'Fill the queue name',
                    default: 'default',
                ),
                comment: 'The name of the queue on which to place the broadcasting job.',
            );

        }

        $broadcastOn = '';
        if($this->option('should-broadcast-now') || $this->option('should-broadcast')) {
            $channelType = select(
                label: 'Select the channel type',
                options: ['Channel', 'PrivateChannel', 'PresenceChannel'],
                default: 'Channel',
            );
            $channelName = text(
                label: 'Fill the channel name',
                default: 'channel-name',
            );
            $broadcastOn = "new {$channelType}('{$channelName}')";
        }

        if(count($implements) > 0) {
            $className = $className . ' implements ' . implode(', ', $implements);
        }

        return (new Stub($this->getStubName(), [
            'NAMESPACE' => $namespace,
            'NAMESPACES' => array_reduce($namespaces, function($carry, $namespace) {
                return $carry . "use $namespace;\n";
            }, ''),
            'CLASS' => $className,
            'ATTRIBUTES' => implode("\n\n", $attributes),
            'METHODS' => implode("\n\n", $methods),
            'BROADCAST_ON' => $broadcastOn,
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $moduleName = $this->getModuleName();
        $fileName = $this->getFileName();

        if (!$moduleName) {
            return base_path("app/Events/{$fileName}.php");
        }

        $module = Modularity::findOrFail($moduleName);

        $targetClassPath = $module->getTargetClassPath('event', $fileName . ".php");

        return $targetClassPath;
    }

    protected function getFileName()
    {
        return Str::studly($this->argument('name'));
    }

    protected function getStubName()
    {
        return '/event.stub';
    }
}
