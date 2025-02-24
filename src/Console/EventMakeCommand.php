<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Finder\Finder;
use Unusualify\Modularity\Facades\Modularity;
use function Laravel\Prompts\{select, text, confirm};

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
        {--self : The path of the modularity}
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

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $moduleName = $this->getModuleName();
        $className = $this->getFileName();
        $self = $this->option('self');
        $namespace = 'App\Events';

        $implements = [];
        $extend = null;
        $namespaces = [];
        $attributes = [];
        $methods = [];

        if($self){
            $namespace = 'Unusualify\Modularity\Events';


        }else if($moduleName) {
            $module = Modularity::findOrFail($moduleName);
            $namespace = $module->getTargetClassNamespace('event');
        }


        $abstractEventPaths = [
            base_path('app/Events'),
            Modularity::getModulePath('/**/Events'),
            Modularity::getVendorPath('/src/Events'),
        ];
        $abstractEventClasses = collect(Finder::create()->files()->depth(0)->in($abstractEventPaths))->reduce(function($carry, $file) {
            $className = get_file_class($file->getRealPath());

            if($className) {
                $reflector = new \ReflectionClass(get_file_class($file->getRealPath()));
                if($reflector->isAbstract()) {
                    $carry[$className] = $file;
                }
            }

            return $carry;
        }, collect());

        if($abstractEventClasses->count() > 0 && confirm('Do you want use a specific abstract event class?', default: false)) {
            $abstractEventClass = select(
                label: 'Select the abstract event class',
                options: $abstractEventClasses->keys()->toArray(),
                default: $abstractEventClasses->keys()->first(),
            );
            $abstractEventClassShortName = get_class_short_name($abstractEventClass);
            $namespaces[] = $abstractEventClass;
            $extend = $abstractEventClassShortName;
        }


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

        if($extend) {
            $className = $className . ' extends ' . $extend;
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
        $fileName = $this->getFileName() . '.php';
        $self = $this->option('self');

        if($self){
            if(Modularity::isProduction()) {
                throw new \Exception('You cannot create an event in the vendor path in production');
            }
            return Modularity::getVendorPath('src/Events/') . "{$fileName}";

        }else if (!$moduleName){
            return base_path("app/Events/{$fileName}");
        }

        $module = Modularity::findOrFail($moduleName);

        $targetClassPath = $module->getTargetClassPath('event', $fileName);

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
