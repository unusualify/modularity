<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Collection;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Generators\RouteGenerator;

class RouteMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:make:route
        {module : The name of module will be used}
        {route : The name of the route}
        {--schema= : The specified migration schema table}
        {--rules= : The specified validation rules for FormRequest}
        {--custom-model= : The model class for usage of a available model}
        {--relationships= : The many to many relationships}
        {--f|force : Force the operation to run when the route files already exist}
        {--p|plain : Don\'t create route}
        {--notAsk : Don\'t ask for trait questions}
        {--all : Add all traits}
        {--no-migrate : Don\'t migrate}
        {--no-defaults : Unuse default input and headers}
        {--fix : Fixes the model config errors}
        {--table-name= : Sets table name for custom model}
        {--no-migration : Don\'t create migration file}
        {--test : Test the Route Generator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create files for routes.';

    protected $aliases = [
        'm:m:r',
        'u:m:r',
        'unusual:make:route',
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

    public $useTraitOptions = true;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Stub::setBasePath(dirname(__FILE__) . '/stubs');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Modularity::disableCache();

        $route = $this->argument('route');

        $module = $this->argument('module');

        $traits = activeModularityTraits($this->options());

        foreach (getModularityTraits() as $_trait) {
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
            ->setMigration($this->option('no-migration'))
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

        Modularity::clearCache();

        return $success ? 0 : E_ERROR;
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
