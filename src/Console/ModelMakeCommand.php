<?php

namespace OoBook\CRM\Base\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;
use OoBook\CRM\Base\Support\Decomposers\ModelRelationParser;
use OoBook\CRM\Base\Support\Decomposers\SchemaParser;
use Nwidart\Modules\Support\Config\GeneratorPath;

use Nwidart\Modules\Generators\FileGenerator;

class ModelMakeCommand extends BaseCommand
{

    protected $name = 'unusual:make:model';

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'model';

    protected $defaultReject = true;

    protected $isAskable = true;

    // protected $signature = 'unusual:make:model
    //     {model : the model name in module}
    //     {module : the module name}
    //     {--fillable=}
    //     {--relationships=}
    //     {--f|force}
    //     {--T|translationTrait}
    //     {--M|mediaTrait}
    //     {--F|fileTrait}
    //     {--P|positionTrait}
    //     {--all}
    //     {--soft-delete}
    //     {--has-factory}
    //     {--notAsk}';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $name = 'unusual:make:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for the specified module.';

    protected $modelTraits;

    protected $defaultFillables = [];

    protected $modelRelationParser;

    public function handle() : int
    {
        // $this->traits = getTraits();
        $this->setAskability();

        // $this->defaultConsent = false;

        foreach(getUnusualTraits() as $_trait){
            $this->responses[$_trait] = $this->checkOption($_trait);
        }

        if(!$this->option('no-defaults')){
            $this->defaultFillables += (new SchemaParser(implode(',', $this->baseConfig('schemas.default_fields') ?? [])))->getColumns();
        }

        if( $this->option('relationships')){
            $this->modelRelationParser = App::makeWith(ModelRelationParser::class, [
                'model' => $this->argument('model'),
                'relations' => $this->option('relationships')
            ]);
        }

        $this->createAdditionalModels();

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        // $this->handleOptionalMigrationOption();
        // $this->handleOptionalControllerOption();
        $this->info('Model created successfully!');

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
            ['model', InputArgument::REQUIRED, 'The name of model will be created.'],
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
            ['fillable', null, InputOption::VALUE_OPTIONAL, 'The fillable attributes.', null],
            ['relationships', null, InputOption::VALUE_OPTIONAL, 'The relationship attributes.', null],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['no-defaults', null, InputOption::VALUE_NONE, 'unuse default input and headers.'],
            ['soft-delete', 's', InputOption::VALUE_NONE, 'Flag to add softDeletes trait to model.'],
            ['has-factory', null, InputOption::VALUE_NONE, 'Flag to add hasFactory to model.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
        ], unusualTraitOptions());
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return  (new Stub( $this->getStubName(), [
            'BASE_MODEL'            => $this->baseConfig('base_model'),
            'NAMESPACE'             => $this->getClassNamespace($module),
            'TRAIT_NAMESPACES'      => $this->getTraitNamespaces(),
            'INTERFACE_NAMESPACES'  => $this->getInterfaceNamespaces(),
            'NAME'                  => $this->getModelName(),
            'CLASS'                 => $this->getClass(),
            'INTERFACES'            => $this->getInterfaces(),
            'TRAITS'                => $this->getTraits(),
            'FILLABLE'              => ltrim($this->getFillable()),
            'TRANSLATED_ATTRIBUTES' => ltrim($this->getTranslatedAttributes()),
            'SLUG_ATTRIBUTES' => ltrim($this->getSlugAttributes()),
            'METHODS'               => $this->getMethods()
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = new GeneratorPath( $this->baseConfig('paths.generator.model') );

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . '.php';
    }

    /**
     * @return mixed|string
     */
    private function getModelName()
    {
        return Str::studly($this->argument('model'));
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace() : string
    {
        return $this->baseConfig('paths.generator.model.namespace') ?:
            $this->baseConfig('paths.generator.model.path', 'Entities');

    }

    public function getInterfaces()
    {
        $interfaces = [];

        foreach ($this->responses as $trait => $status) {
            if($status)
                $interfaces[] = $this->getInterface($trait);
        }

        $interfaces = call_user_func_array('array_merge', $interfaces);

        return count($interfaces) ? "implements ".implode(',',$interfaces) : '';
    }

    public function getInterfaceNamespaces()
    {
        $interfaces = [];

        foreach ($this->responses as $trait => $status) {

            if($status)
                $interfaces[] = $this->getInterfaceNamespace($trait);
        }

        $interfaces = call_user_func_array('array_merge', $interfaces);

        $namespaces = array_map(function($v){
            return "use $v;\n";
        }, $interfaces );

        return count($namespaces) ? implode('', $namespaces) : '';
    }

    private function getFillable() : string
    {
        $defaultFillableSchema = implode(',',$this->baseConfig('schemas.fillables'));

        $fields = (new SchemaParser($defaultFillableSchema))->getColumns();

        if(!$this->getTraitResponse('addTranslation')){
            $fields = array_merge($this->defaultFillables, $fields);

            $fillable = $this->option('fillable');
            $fields = array_merge( $fields, $fillable != "" ? explode(',', $fillable) : []);
        }

        return $this->generateFillable($fields);
    }

    private function generateFillable($fields) {
        $fillable = "\t/**\n"
        . "\t * The attributes that are mass assignable.\n"
        . "\t * \n"
        . "\t * @var array<int, string>\n"
        . "\t */ \n";

        $fillable .= "\tprotected \$fillable = [\n"
            . collect($fields)->map(function($field){
                return "\t\t'{$field}'";
            })->implode(",\n")."\n"
            . "\t];\n";

        return $fillable;
    }

    private function generateCasts($fields) {
        $fillable = "";

        $fillable .= "\tprotected \$casts = [\n"
            . collect($fields)->map(function($type, $field){
                return "\t\t'{$field}' => '{$type}'";
            })->implode(",\n")."\n"
            . "\t];\n";

        return $fillable;
    }

    private function getTranslatedAttributes() : string
    {
        $attributes = "";

        if($this->getTraitResponse('addTranslation')){
            $fillable = $this->option('fillable');

            $fields = array_merge( $this->defaultFillables, explode(',', $fillable));

            $attributes = "\t/**\n"
                . "\t * The translated attributes that are assignable for hasTranslation Trait.\n"
                . "\t * \n"
                . "\t * @var array<int, string>\n"
                . "\t */ \n";

            $defaultTranslatedSchema = implode(',',$this->baseConfig('schemas.translated_attributes'));

            $fields = array_merge( $fields, (new SchemaParser($defaultTranslatedSchema))->getColumns() );

            $attributes .= "\tpublic \$translatedAttributes = [\n"
                . collect($fields )->map(function($field){
                    return "\t\t'{$field}'";
                })->implode(",\n")."\n"
                . "\t]; \n";
        }

        return $attributes;
    }

    private function getSlugAttributes() : string
    {
        $attributes = "";

        if($this->getTraitResponse('addSlug')){

            $fields[] = $this->defaultFillables[0];

            $attributes = "\t/**\n"
                . "\t * The slug attributes that are assignable for hasSlug Trait.\n"
                . "\t * \n"
                . "\t * @var array<int, string>\n"
                . "\t */ \n";


            $attributes .= "\tprotected \$slugAttributes = [\n"
                . collect($fields )->map(function($field){
                    return "\t\t'{$field}'";
                })->implode(",\n")."\n"
                . "\t]; \n";
        }

        return $attributes;
    }

    /**
     * @return string
     */
    private function getTraits()
    {
        $traits = [];

        if( $this->option('soft-delete') ){
            $traits[] = $this->getTrait('soft_delete');
        }

        if( $this->option('has-factory') ){
            $traits[] = $this->getTrait('has_factory');
        }

        foreach ($this->responses as $trait => $status) {
            if($status)
                $traits[] = $this->getTrait($trait);
        }

        return count($traits) ? "use ".implode(', ',$traits).";\n" : '';
    }

    /**
     * @return string
     */
    private function getTraitNamespaces()
    {
        $namespaces = [];

        if( $this->option('soft-delete') ){
            $namespaces[] = $this->getTraitNamespace('soft_delete');
        }

        if( $this->option('has-factory') ){
            $namespaces[] = $this->getTraitNamespace('has_factory');
        }

        foreach ($this->responses as $trait => $status) {
            if($status)
                $namespaces[] = $this->getTraitNamespace($trait);
        }

        $namespaces = array_map(function($v){
            return "use $v;\n";
        }, $namespaces );
        return count($namespaces) ? implode('', $namespaces) : '';
    }

    private function getMethods()
    {
        $methods = [];

        if( $this->option('has-factory') ){
            $module_namespace = $this->laravel['modules']->config('namespace');
            $module = $this->getModuleName();
            $name = $this->getModelName();

            $str = "\\{$module_namespace}\\{$module}\\Database\\factories\\{$name}Factory";

            if( @class_exists($str) ){
                $methods[] = "\tprotected static function newFactory()\n\t{\n\t\treturn {$str}::new();\n\t}";
            }
            // dd($str, class_exists($str), $methods);
        }

        if( $this->option('relationships')){
            $methods = array_merge($methods, $this->modelRelationParser->render());
        }

        return count($methods) ? implode("\n", $methods) : '';
    }

    /**
     * Get Namespace of Interface.
     *
     * @return string
     */
    public function getInterfaceNamespace($trait)
    {
        return $this->getSchemaParser()->getInterfaceNamespaces($trait);
    }
    /**
     * Get Name of Interface.
     *
     * @return string
     */
    public function getInterface($trait)
    {
        return $this->getSchemaParser()->getInterfaces($trait);
    }
    /**
     * Get Namespace of Trait.
     *
     * @return string
     */
    public function getTraitNamespace($trait)
    {
        return $this->getSchemaParser()->getTraitNamespace($trait);
    }

    /**
     * Get Name of Trait.
     *
     * @return string
     */
    public function getTrait($trait)
    {
        return $this->getSchemaParser()->getTrait($trait);
    }

    /**
     * @return string
     */
    protected function getStubName(): string
    {
        return '/models/model.stub';
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

        $questions = [
            // 'hasBlocks' => 'Do you need to use the block editor on this module?',
            'addTranslation' => 'Do you need to translate content on this module?',
            // 'slugTrait' => 'Do you need to generate slugs on this module?',
            'addMedia' => 'Do you need to attach images on this module?',
            'addFile' => 'Do you need to attach files on this module?',
            'addPosition' => 'Do you need to manage the position of records on this module?',
            // 'revisionsTrait' => 'Do you need to enable revisions on this module?',
            // 'nestingTrait' => 'Do you need to enable nesting on this module?',
        ];

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

    private function createAdditionalModels()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;

        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = new GeneratorPath( $this->baseConfig('paths.generator.model') );

        if($this->modelRelationParser->isCreatablePivotModel()) {
            $pivot_models = $this->modelRelationParser->getPivotModels();

            foreach ($pivot_models as $key => $pivot_model) {
                $content = (new Stub( '/models/pivot_model.stub', [
                    'NAMESPACE' => $this->getClassNamespace($module),
                    'CLASS'     => $pivot_model['class'],
                    'CASTS'     => $this->generateCasts($pivot_model['casts']),
                    'FILLABLE'  => $this->generateFillable($pivot_model['fillables']),
                ]))->render();

                $fullPath = $path . $modelPath->getPath() . "" . '/' . $pivot_model['class'] . '.php';

                if (!$this->laravel['files']->isDirectory($dir = dirname($fullPath))) {
                    $this->laravel['files']->makeDirectory($dir, 0777, true);
                }

                (new FileGenerator($fullPath, $content))->withFileOverwrite($overwriteFile)->generate();
            }
        }

        if($this->getTraitResponse('addTranslation')){
            $content = (new Stub( '/models/translation_model.stub', [
                'NAMESPACE'         => $this->getClassNamespace($module)."\\Translations",
                'BASE_MODEL'        => $this->baseConfig('base_model'),
                'MODEL_NAMESPACE'   => $this->getClassNamespace($module)."\\".$this->getModelName(),
                'TRANSLATION_CLASS' => $this->getModelName()."Translation",
                'MODEL_CLASS'       => $this->getClass(),
            ]))->render();

            $fullPath = $path . $modelPath->getPath() . "/Translations" . '/' . $this->getModelName() . 'Translation.php';

            if (!$this->laravel['files']->isDirectory($dir = dirname($fullPath))) {
                $this->laravel['files']->makeDirectory($dir, 0777, true);
            }

            (new FileGenerator($fullPath, $content))->withFileOverwrite($overwriteFile)->generate();
        }

        if( $this->getTraitResponse('addSlug') ){

            $content = (new Stub( '/models/slug_model.stub', [
                'NAMESPACE'         => $this->getClassNamespace($module)."\\Slugs",
                'BASE_MODEL'        => $this->baseConfig('base_model'),
                'SLUG_CLASS'        => $this->getModelName()."Slug",
                'MODEL_SNAKE'       => Str::snake($this->getClass()),
            ]))->render();

            $fullPath = $path . $modelPath->getPath() . "/Slugs" . '/' . $this->getModelName() . 'Slug.php';

            if (!$this->laravel['files']->isDirectory($dir = dirname($fullPath))) {
                $this->laravel['files']->makeDirectory($dir, 0777, true);
            }

            (new FileGenerator($fullPath, $content))->withFileOverwrite($overwriteFile)->generate();
        }
    }

}
