<?php

namespace OoBook\CRM\Base\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Nwidart\Modules\Support\Stub;
use OoBook\CRM\Base\Generators\RouteGenerator;

class RouteMakeCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'unusual:make:route
    //     {route : the route name in module}
    //     {module : the module name}
    //     {--schema=}
    //     {--rules=}
    //     {--notAsk}
    //     {--f|force}
    //     {--T|translationTrait}
    //     {--M|mediaTrait}
    //     {--F|fileTrait}
    //     {--P|positionTrait}
    //     {--all}';
    // protected $signature = 'unusual:make:route {name} {module}
    //     {--B|hasBlocks}
    //     {--T|hasTranslation}
    //     {--S|hasSlug}
    //     {--M|hasMedias}
    //     {--F|hasFiles}
    //     {--P|hasPosition}
    //     {--R|hasRevisions}
    //     {--N|hasNesting}
    //     {--all}';

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
        // dd($name, $module);

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
        return [
            ['schema', null, InputOption::VALUE_OPTIONAL, 'The specified migration schema table.', null],
            ['rules', null, InputOption::VALUE_OPTIONAL, 'The specified validation rules for FormRequest.', null],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
        ] + unusualTraitOptions();
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
