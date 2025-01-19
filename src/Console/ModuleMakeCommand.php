<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
// use Illuminate\Console\Command as Console;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Facades\Modularity;

class ModuleMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a module';

    protected $aliases = [
        'm:m:m',
        'unusual:make:module',
    ];

    /**
     * The laravel console instance.
     *
     * @var Console
     */
    protected $console;

    private function getPlainOption()
    {
        return ! (
            $this->option('relationships')
            || $this->option('schema')
            || $this->option('rules')
            || $this->option('no-plain')
        );
    }

    public function handle(): int
    {
        Modularity::disableCache();

        if ($this->option('just-stubs')) {
            $module = Modularity::find($this->argument('module'));

            foreach ($module->getRoutes() as $key => $routeName) {
                $this->call('modularity:make:stubs', [
                    'module' => $module->getName(),
                    'route' => $routeName,
                    '--fix' => true,
                    '--only' => $this->option('stubs-only'),
                    '--except' => $this->option('stubs-except'),
                ]);
            }

            return 0;

        }

        $traits = activeUnusualTraits($this->options());

        // foreach(getUnusualTraits() as $_trait){
        //     $this->responses[$_trait] = $this->checkOption($_trait);
        // }

        $console_traits = collect($traits)->mapWithKeys(function ($item, $key) {
            return ["--{$key}" => $item];
        })->toArray();

        $this->call('module:make', [
            'name' => [$this->argument('module')],
            '--plain' => true,
        ]);

        $this->call('modularity:make:route', [
            'module' => $this->argument('module'),
            'route' => $this->argument('module'),
        ]
            + ($this->hasOption('schema') ? ['--schema' => $this->option('schema')] : [])
            + ($this->hasOption('rules') ? ['--rules' => $this->option('rules')] : [])
            + ($this->hasOption('relationships') ? ['--relationships' => $this->option('rules')] : [])
            + ($this->option('force') ? ['--force' => true] : [])
            + ($this->option('no-migrate') ? ['--no-migrate' => true] : [])
            + ($this->option('no-defaults') ? ['--no-defaults' => true] : [])
            + ($this->option('no-migration') ? ['--no-migration' => true] : [])
            + ($this->option('table-name') ? ['--table-name' => $this->option('table-name')] : [])
            + (['-p' => $this->getPlainOption()])
            + $console_traits
            + ['--notAsk' => true]
            + ['--test' => false]
        );

        Modularity::clearCache();

        return 0;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of the module.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge([
            ['schema', null, InputOption::VALUE_OPTIONAL, 'The specified migration schema table.', null],
            ['rules', null, InputOption::VALUE_OPTIONAL, 'The specified validation rules for FormRequest.', null],
            ['relationships', null, InputOption::VALUE_OPTIONAL, 'The many to many relationships.', null],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            // ['plain', null, InputOption::VALUE_NONE, 'Don\'t create route.'],
            ['no-plain', null, InputOption::VALUE_NONE, 'Create route.'],
            ['no-migrate', null, InputOption::VALUE_NONE, 'don\'t migrate.'],
            ['no-defaults', null, InputOption::VALUE_NONE, 'unuse default input and headers.'],
            ['no-migration', null, InputOption::VALUE_NONE, 'don\'t create migration file.'],
            ['custom-model', null, InputOption::VALUE_OPTIONAL, 'The model class for usage of a available model.', null],
            ['table-name', null, InputOption::VALUE_OPTIONAL, 'Sets table  name for custom model'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
            ['just-stubs', null, InputOption::VALUE_NONE, 'only stubs fix'],
            ['stubs-only', null, InputOption::VALUE_OPTIONAL, 'Get only stubs'],
            ['stubs-except', null, InputOption::VALUE_OPTIONAL, 'Get except stubs'],
        ], unusualTraitOptions());
    }

    /**
     * Get head string of path for namespace
     */
    public function getDefaultNamespace(): string
    {
        // dd($this->baseConfig('paths.generator.controller-api.namespace'), $this->baseConfig('paths.generator.controller-api.path', 'Https\Controllers\API'));
        return $this->baseConfig('paths.generator.route-request.namespace') ?:
            $this->baseConfig('paths.generator.route-request.path', 'Http\Requests');
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('module'));
    }

    private function checkOption($option)
    {

        if (! $this->hasOption($option)) {
            return false;
        }

        if ($this->option($option) || $this->option('all')) {
            return true;
        }

        if (! $this->isAskable()) {
            return false;
        }

        $questions = Collection::make($this->baseConfig('traits'))->mapWithKeys(function ($object, $key) {
            return [$key => $object['question']];
        })->toArray();

        $defaultAnswers = [
            'nestingTrait' => 0,
        ];

        $currentDefaultAnswer = $this->defaultReject ? 0 : ($defaultAnswers[$option] ?? 1);

        // dd(
        //     $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer)
        // );
        return $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer) === 'yes';
    }
}
