<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularity\Facades\Modularity;

class RepositoryMakeCommand extends BaseCommand
{
    protected $name = 'modularity:make:repository';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:make:repository
        {repository : The name of the repository class}
        {module : The name of module will be used}
        {--f|force : Force the operation to run when the route files already exist}
        {--custom-model= : The model class for usage of a available model}
        {--notAsk : Don\'t ask for trait questions}
        {--all : Add all traits}';

    protected $defaultReject = true;

    protected $isAskable = true;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class for the specified module.';

    protected $argumentName = 'repository';

    public $useTraitOptions = true;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // $this->traits = getTraits();
        $this->setAskability();

        // $this->defaultConsent = false;
        foreach (getModularityTraits() as $_trait) {
            $this->responses[$_trait] = $this->checkOption($_trait);
        }

        $isSingularExceptionTraits = [
            'addTranslation',
            'addSnapshot',
        ];

        foreach ($this->responses as $trait => $response) {
            if ($response) {
                if (in_array($trait, $isSingularExceptionTraits) && $this->getTraitResponse('addSingular')) {
                    $this->responses[$trait] = false;
                }
            }
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
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $repository = $this->argument('repository');
        $module = Modularity::findOrFail($this->getModuleName());

        $modelName = $this->getFileName() ?? '';
        $modelClass = $this->getModelClass() ?? '';

        if ($this->option('custom-model') && @class_exists($this->option('custom-model'))) {
            $modelName = get_class_short_name(App::make($this->option('custom-model')));
            $modelClass = $this->option('custom-model');
        }

        return (new Stub($this->getStubName(), [
            'BASE_REPOSITORY' => $this->baseConfig('base_repository'),
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClass() . 'Repository',
            'STUDLY_NAME' => $this->getStudlyName($repository),
            'MODEL_CLASS' => $modelClass,
            'MODEL_NAME' => $modelName,
            'MODULE' => $this->argument('module'),
            'TRAITS' => $this->getTraits(),
            'TRAIT_NAMESPACES' => $this->getTraitNamespaces(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = Modularity::getModulePath($this->getModuleName());

        $repositoryPath = GenerateConfigReader::read('repository');

        // dd($path, $repositoryPath->getPath(), Modularity::getModulePath($this->getModuleName()) );
        return $path . $repositoryPath->getPath() . '/' . $this->getFileName() . 'Repository.php';
    }

    public function getDefaultNamespace(): string
    {
        return $this->baseConfig('paths.generator.repository.namespace', 'Repositories') ?:
            $this->baseConfig('paths.generator.repository.path', 'Repositories');
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        // if($this->option('custom-model') && @class_exists($this->option('custom-model'))){
        //     return $this->option('custom-model');
        // }
        return Str::studly($this->argument('repository'));
    }

    protected function getStubName(): string
    {
        return '/route-repository.stub';
    }

    /**
     * @return class
     */
    private function getModelClass()
    {
        $module = Modularity::findOrFail($this->getModuleName());

        // dd( config('modules.namespace'), $module->getName(), get_class_methods($module));
        $module_name = $module->getStudlyName();

        if ($this->option('custom-model') && @class_exists($this->option('custom-model'))) {
            return $this->option('custom-model');
        }

        $model_name = $this->getFileName();

        return config('modules.namespace') . "\\{$module_name}\\Entities\\{$model_name}";
    }

    /**
     * @return string
     */
    private function getTraits()
    {
        $traits = [];

        foreach ($this->responses as $trait => $status) {
            if ($status && ($t = $this->getTrait($trait)) != '') {
                $traits[] = $this->getTrait($trait);
            }
        }

        return count($traits) ? 'use ' . implode(',', $traits) . ';' : '';
    }

    /**
     * @return string
     */
    private function getTraitNamespaces()
    {
        $namespaces = [];

        foreach ($this->responses as $trait => $status) {
            if ($status && ($t = $this->getTraitNamespace($trait)) != '') {
                $namespaces[] = $this->getTraitNamespace($trait);
            }
        }

        $namespaces = array_map(function ($v) {
            return "use $v;\n";
        }, $namespaces);

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
