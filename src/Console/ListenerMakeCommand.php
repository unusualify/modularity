<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Finder\Finder;
use Unusualify\Modularity\Facades\Modularity;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ListenerMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:make:listener
		{name : The name of listener}
		{module? : The name of module}
        {--self : Create a modularity listener}
        {--f|force : Overwrite existing file}
        {--should-handle-events-after-commit : Should the event handle events after commit}
        {--should-queue : Should the event be queued}';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Laravel Listener';

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

        $namespace = 'App\Listeners';

        if ($self) {
            $namespace = Modularity::getVendorNamespace('/src/Listeners');
        } elseif ($moduleName) {
            $module = Modularity::findOrFail($moduleName);
            $namespace = $module->getTargetClassNamespace('listener');
        }

        $paths = [
            base_path('app/Events'),
            Modularity::getModulePath('/**/Events'),
            Modularity::getVendorPath('/src/Events'),
        ];

        $implements = [];
        $namespaces = [];
        $attributes = [];
        $methods = [];
        $traits = [];
        $eventParameter = '$event';

        $events = collect(Finder::create()->files()->depth(0)->in($paths))->reduce(function ($carry, $file) {
            $content = get_file_string($file->getRealPath());
            $className = get_file_class($file->getRealPath());

            if ($className) {
                $reflector = new \ReflectionClass(get_file_class($file->getRealPath()));
                if (! $reflector->isAbstract() && ! $reflector->isInterface()) {
                    $carry[$className] = $file;
                }
            }

            return $carry;
        }, collect());

        if ($events->count() > 0) {
            $eventClass = select(
                label: 'Select the event class',
                options: $events->keys()->toArray(),
                default: $events->keys()->first(),
            );

            $eventClassShortName = get_class_short_name($eventClass);
            $eventParameter = $eventClassShortName . ' $event';
            $namespaces[] = $eventClass;
        } else {
            $this->warn('No event found');
        }

        if ($this->option('should-queue')) {
            $implements[] = 'ShouldQueue';
            $namespaces[] = 'Illuminate\Contracts\Queue\ShouldQueue';
            $namespaces[] = 'Illuminate\Queue\InteractsWithQueue';
            $traits[] = 'InteractsWithQueue';

            $attributes[] = attribute_string(
                attribute_name: 'connection',
                value: select(
                    label: 'Select the queue connection?',
                    options: array_keys(config('queue.connections')),
                    default: 'redis',
                ),
                comment: 'The name of the connection the job should be sent to.',
                customVarType: 'string|null',
            );

            $attributes[] = attribute_string(
                attribute_name: 'queue',
                value: text(
                    label: 'Fill the queue name',
                    default: 'default',
                ),
                comment: 'The name of the queue the job should be sent to.',
                customVarType: 'string|null',
            );

            $attributes[] = attribute_string(
                attribute_name: 'delay',
                value: (int) text(
                    label: 'Fill the delay time in seconds before the job should be processed',
                    default: '60',
                ),
                comment: 'The time (seconds) before the job should be processed.',
                customVarType: 'int',
            );

            $attributes[] = attribute_string(
                attribute_name: 'tries',
                value: (int) text(
                    label: 'Fill the number of times the queued listener may be attempted',
                    default: '5',
                ),
                comment: 'The number of times the queued listener may be attempted.',
                customVarType: 'int',
            );

            $methods[] = method_string(
                method_name: 'shouldQueue',
                parameters: [
                    $eventParameter,
                ],
                content: [
                    'return true;',
                ],
                comment: 'Determine whether the listener should be queued.',
                return_type: 'bool'
            );
        }

        if ($this->option('should-handle-events-after-commit')) {
            $implements[] = 'ShouldHandleEventsAfterCommit';
            $namespaces[] = 'Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit';
        }

        if (count($implements) > 0) {
            $className = $className . ' implements ' . implode(', ', $implements);
        }

        return (new Stub($this->getStubName(), [
            'NAMESPACE' => $namespace,
            'NAMESPACES' => array_reduce($namespaces, function ($carry, $namespace) {
                return $carry . "use $namespace;\n";
            }, ''),
            'CLASS' => $className,
            'EVENT_PARAMETER' => $eventParameter,
            'TRAITS' => count($traits) ? indent(string: 'use ' . implode(', ', $traits) . ";\n") : '',
            'ATTRIBUTES' => implode("\n\n", $attributes),
            'METHODS' => implode("\n\n", $methods),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $moduleName = $this->getModuleName();
        $path = base_path('app/Listeners');
        $fileName = $this->getFileName() . '.php';
        $self = $this->option('self');

        if (! $moduleName) {
            if ($self) {
                return Modularity::getVendorPath("/src/Listeners/{$fileName}");
            }

            return base_path("app/Listeners/{$fileName}");
        }

        $module = Modularity::findOrFail($moduleName);

        $targetClassPath = $module->getTargetClassPath('listener', $fileName);

        return $targetClassPath;
    }

    protected function getFileName()
    {
        return Str::studly($this->argument('name'));
    }

    protected function getStubName()
    {
        return '/listener.stub';
    }
}
