<?php

namespace OoBook\CRM\Base\Console;

use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Str;

// use Illuminate\Console\Command as Console;
use Illuminate\Support\Collection;

class ModuleMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a module';

    // protected $argumentName = 'request';

    /**
     * The laravel console instance.
     *
     * @var Console
     */
    protected $console;

    public function handle() : int
    {

        // $console = new Console();

        $traits = activeUnusualTraits($this->options());

        foreach(getUnusualTraits() as $_trait){
            $this->responses[$_trait] = $this->checkOption($_trait);
        }

        $console_traits =  collect($traits)->mapWithKeys(function ($item, $key) {
            return ["--{$key}" => $item];
        })->toArray();

        $this->call('module:make',[
            'name' => [$this->argument('module')],
            '--plain' => true
        ]);

        $this->call('unusual:make:route', [
                'module' => $this->argument('module'),
                'route' => $this->argument('module'),
            ]
            + ( $this->hasOption('schema') ?  ['--schema' => $this->option('schema')] : [])
            + ( $this->hasOption('rules') ?  ['--rules' => $this->option('rules')] : [])
            + ( $this->hasOption('force') ?  ['--force' => true] : [])
            + $console_traits
            + ['--notAsk' => true]
        );

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
        return [
            ['schema', null, InputOption::VALUE_OPTIONAL, 'The specified migration schema table.', null],
            ['rules', null, InputOption::VALUE_OPTIONAL, 'The specified validation rules for FormRequest.', null],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
        ] + unusualTraitOptions();
    }

    /**
     *
     * Get head string of path for namespace
     *
     * @return string
     */
    public function getDefaultNamespace() : string
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

        if ( !$this->isAskable() )
            return false;

        $questions = Collection::make($this->baseConfig('traits'))->mapWithKeys(function($object, $key){
            return [ $key => $object['question']];
        })->toArray();

        $defaultAnswers = [
            'nestingTrait' => 0,
        ];

        $currentDefaultAnswer = $this->defaultReject ? 0 : ($defaultAnswers[$option] ?? 1);
        // dd(
        //     $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer)
        // );
        return 'yes' === $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer);
    }

}
