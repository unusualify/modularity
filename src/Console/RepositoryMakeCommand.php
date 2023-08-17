<?php

namespace OoBook\CRM\Base\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Illuminate\Support\Str;

class RepositoryMakeCommand extends BaseCommand
{

    protected $name = 'unusual:make:repository';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $name = 'unusual:make:repository';

    protected $defaultReject = true;

    protected $isAskable = true;

    // protected $signature = 'unusual:make:repository
    //     {repository : the repository name in module}
    //     {module : the module name}
    //     {--f|force}
    //     {--T|translationTrait}
    //     {--M|mediaTrait}
    //     {--F|fileTrait}
    //     {--P|positionTrait}
    //     {--all}
    //     {--notAsk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class for the specified module.';

    protected $argumentName = 'repository';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle() : int
    {
        // $this->traits = getTraits();
        $this->setAskability();

        // $this->defaultConsent = false;
        foreach(getUnusualTraits() as $_trait){
            $this->responses[$_trait] = $this->checkOption($_trait);
        }

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        // $this->handleOptionalMigrationOption();
        // $this->handleOptionalControllerOption();
        $this->info('Repository created successfully!');

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
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['repository', InputArgument::REQUIRED, 'The name of the repository class.'],
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
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
        ] + unusualTraitOptions();
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $repository = $this->argument('repository');
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());
        // dd( $this->getClassNamespace($module), $this->getClass(), $this->getStubName(), get_class($module), $module->getPath(), Stub::getBasePath() );
        // dd(
        //     (new Stub($this->getStubName(), [
        //         'BASE_REPOSITORY'       => $this->baseConfig('base_repository'),
        //         'NAMESPACE'             => $this->getClassNamespace($module),
        //         'CLASS'                 => $this->getClass().'Repository',
        //         'STUDLY_NAME'           => $this->getStudlyName($repository),
        //         'MODEL_CLASS'           => $this->getModelClass() ?? '',
        //         'MODEL_NAME'           => $this->getFileName() ?? '',
        //         'MODULE'                => $this->argument('module'),
        //         'TRAITS'                => $this->getTraits(),
        //         'TRAIT_NAMESPACES'      => $this->getTraitNamespaces(),
        //     ]))->render()
        // );
        return (new Stub($this->getStubName(), [
            'BASE_REPOSITORY'       => $this->baseConfig('base_repository'),
            'NAMESPACE'             => $this->getClassNamespace($module),
            'CLASS'                 => $this->getClass().'Repository',
            'STUDLY_NAME'           => $this->getStudlyName($repository),
            'MODEL_CLASS'           => $this->getModelClass() ?? '',
            'MODEL_NAME'            => $this->getFileName() ?? '',
            'MODULE'                => $this->argument('module'),
            'TRAITS'                => $this->getTraits(),
            'TRAIT_NAMESPACES'      => $this->getTraitNamespaces(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $repositoryPath = GenerateConfigReader::read('repository');

        // dd($path, $repositoryPath->getPath(), $this->laravel['modules']->getModulePath($this->getModuleName()) );

        return $path . $repositoryPath->getPath() . '/' . $this->getFileName() . 'Repository.php';
    }

    public function getDefaultNamespace() : string
    {
        return $this->baseConfig('paths.generator.repository.namespace', 'Repositories') ?:
            $this->baseConfig('paths.generator.repository.path', 'Repositories');
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('repository'));
    }


    /**
     * @return string
     */
    protected function getStubName(): string
    {
        return '/route-repository.stub';
    }

    /**
     * @return class
     */
    private function getModelClass()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        // dd( config('modules.namespace'), $module->getName(), get_class_methods($module));
        $module_name = $module->getStudlyName();

        $model_name = $this->getFileName();

        return config('modules.namespace')."\\{$module_name}\\Entities\\{$model_name}";
    }

    /**
     * @return string
     */

     private function getTraits()
    {
        $traits = [];

        foreach ($this->responses as $trait => $status) {
            if($status && ($t = $this->getTrait($trait)) != '')
                $traits[] = $this->getTrait($trait);
        }

        return count($traits) ? "use ".implode(',',$traits).";" : '';
    }

    /**
     * @return string
     */
    private function getTraitNamespaces()
    {
        $namespaces = [];


        foreach ($this->responses as $trait => $status) {
            if($status && ($t = $this->getTraitNamespace($trait)) != '')
                $namespaces[] = $this->getTraitNamespace($trait);
        }

        $namespaces = array_map(function($v){
            return "use $v;\n";
        }, $namespaces );

        return count($namespaces) ? implode('', $namespaces) : '';
    }


    /**
     * Get Namespace of Trait.
     *
     * @return string
     */
    public function getTraitNamespace($trait)
    {
        return $this->getSchemaParser()->getRepositoryTraitNamespace($trait);
    }

    /**
     * Get Namespace of Trait.
     *
     * @return string
     */
    public function getTrait($trait)
    {
        return $this->getSchemaParser()->getRepositoryTrait($trait);
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

        if(!$this->isAskable())
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
