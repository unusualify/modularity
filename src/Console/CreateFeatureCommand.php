<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class CreateFeatureCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:create:feature
        {name? : The name of the feature to be created.}';

    protected $aliases = [
        'mod:c:feature',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a modularity feature';

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
    public function handle(): int
    {
        // handle command
        $name = $this->argument('name');

        if (! $name) {
            $name = text('What is the name of the feature?');
        }
        $studlyName = Str::studly($name);

        // Repository Trait
        if (confirm(
            label: 'Do you want to create a repository trait for this feature?',
            default: false
        )) {
            $this->call('modularity:create:repository:trait', ['name' => $name]);
        }

        // Model Trait
        if (confirm(
            label: 'Do you want to create a model trait for this feature?',
            default: false
        )) {
            $this->call('modularity:create:model:trait', ['name' => $name]);
        }

        // Model and Migration
        if (confirm(
            label: 'Do you want to create a model and migration for this feature?',
            default: false
        )) {
            $modelName = Str::studly(text('What will be the name of the model?'));

            $this->call('modularity:make:model', ['model' => $modelName, '--no-defaults' => true]);

            $tableName = tableName($modelName);

            $this->call('modularity:make:migration', ['name' => "create_{$tableName}_table", '--no-defaults' => true]);
        }

        // Vue Input Component
        if (confirm(
            label: 'Do you want to create a vue input component for this feature?',
            default: false
        )) {
            $componentName = Str::studly(text('What will be the name of the input component?', default: $studlyName));

            $this->call('modularity:create:vue:input', ['name' => $componentName]);

            // Vue Component Test
            if (confirm(
                label: 'Do you want to create a vue component test for this input component?',
                default: false
            )) {
                $this->call('modularity:create:vue:test', ['name' => Str::kebab("VInput$componentName"), 'type' => 'component']);
            }

            // Input Hydrate Class
            if (confirm(
                label: 'Do you want to create a input hydrate class for this feature?',
                default: false
            )) {
                // $hydrateName = Str::studly(text('What will be the name of the input hydrate class?'));

                $this->call('modularity:create:input:hydrate', ['name' => $componentName]);
            }
        }


        $this->info('Feature created successfully');

        return 0;
    }
}
