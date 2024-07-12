<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Nwidart\Modules\Support\Stub;
use Unusualify\Modularity\Generators\RouteGenerator;

class RouteMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:make:route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create files for routes.';

    protected $aliases= [
        'u:m:r',
        'modularity:make:route',
    ];

    protected $responses = [];

    /**
     * @var string[]
     */
    protected $modelTraits;

    /**
     * @var string[]
     */
    protected $repositoryTraits;

    protected $defaultReject = true;

    protected $isAskable = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Stub::setBasePath(dirname(__FILE__).'/stubs');
    }

    /**
     * Execute the console command.
     */
    public function handle() : int
    {

        $route = $this->argument('route');

        $module = $this->argument('module');


        $traits = activeUnusualTraits($this->options());

        foreach(getUnusualTraits() as $_trait){
            $this->responses[$_trait] = $this->checkOption($_trait);
        }

        $success = true;
        $code = with(new RouteGenerator($route))
            ->setFilesystem($this->laravel['files'])
            ->setConfig($this->laravel['config'])
            ->setConsole($this)
            ->setTraits($traits)
            ->setForce($this->option('force'))
            ->setModule($module)
            ->setSchema($this->option('schema'))
            ->setRules($this->option('rules'))
            ->setRelationships($this->option('relationships'))
            ->setMigrate($this->option('no-migrate'))
            ->setUseDefaults($this->option('no-defaults'))
            ->setPlain($this->option('plain'))
            ->setCustomModel($this->option('custom-model'))
            ->setFix($this->option('fix'))
            ->setTest($this->option('test'))
            ->setTableName($this->option('table-name'))
            ->generate();

        if ($code === E_ERROR) {
            $success = false;
        }

        return $success ? 0 : E_ERROR;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['route', InputArgument::REQUIRED, 'The name of the route.'],
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
            ['custom-model', null, InputOption::VALUE_OPTIONAL, 'The model class for usage of a available model.', null],
            ['relationships', null, InputOption::VALUE_OPTIONAL, 'The many to many relationships.', null],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['plain', '--p', InputOption::VALUE_NONE, 'Don\'t create route.'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
            ['no-migrate', null, InputOption::VALUE_NONE, 'don\'t migrate.'],
            ['no-defaults', null, InputOption::VALUE_NONE, 'unuse default input and headers.'],
            ['fix', null, InputOption::VALUE_NONE, 'Fixes the model config errors'],
            ['table-name', null, InputOption::VALUE_OPTIONAL, 'Sets table  name for custom model'],
            ['test', null, InputOption::VALUE_NONE, 'Test the Route Generator'],
        ], unusualTraitOptions());
    }

    private function checkOption($option)
    {
        // dd(
        //     $this->options(),
        //     $option,
        //     $this->hasOption($option),
        //     // $this->option($option)
        // );
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
