<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
// use Illuminate\Console\Command as Console;
use Unusualify\Modularity\Exceptions\ModularitySystemPathException;
use Unusualify\Modularity\Facades\Modularity;

class ModuleMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:make:module
        {module : The name of the module}
        {--schema= : The specified migration schema table}
        {--rules= : The specified validation rules for FormRequest}
        {--relationships= : The many to many relationships}
        {--system : Create a system module}
        {--f|force : Force the operation to run when the route files already exist}
        {--no-plain : Create route}
        {--no-migrate : Don\'t migrate}
        {--no-defaults : Unuse default input and headers}
        {--no-migration : Don\'t create migration file}
        {--custom-model= : The model class for usage of a available model}
        {--table-name= : Sets table name for custom model}
        {--notAsk : Don\'t ask for trait questions}
        {--all : Add all traits}
        {--just-stubs : Only stubs fix}
        {--stubs-only= : Get only stubs}
        {--stubs-except= : Get except stubs}';

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

    public $useTraitOptions = true;

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
        if ($this->option('system')) {
            try {
                Modularity::setSystemModulesPath();

            } catch (ModularitySystemPathException $e) {
                $this->error('You cannot create system module because of modularity production');

                return 0;
            }
        }

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

        $traits = activeModularityTraits($this->options());
        // foreach(getModularityTraits() as $_trait){
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
