<?php

namespace OoBook\CRM\Base\Generators;

use Nwidart\Modules\Generators\Generator;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use OoBook\CRM\Base\Support\Decomposers\SchemaParser;
use OoBook\CRM\Base\Traits\Namable;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Nwidart\Modules\Support\Stub;
use Illuminate\Container\Container;


class RouteGenerator extends Generator
{
    use Namable;

    /**
     * The route name will created.
     *
     * @var string
     */
    protected $name;

    /**
     * The laravel service container.
     *
     * @var Config
     */
    protected $app;
    /**
     * The laravel config instance.
     *
     * @var Config
     */
    protected $config;

    /**
     * The laravel filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The laravel console instance.
     *
     * @var Console
     */
    protected $console;

    /**
     * The module instance.
     *
     * @var \Nwidart\Modules\Facades\Module
     */
    protected $module;

    /**
     * Force status.
     *
     * @var bool
     */
    protected $force = false;

    /**
     * set default module type.
     *
     * @var string
     */
    protected $type = 'web';

    /**
     * Schema for migration.
     *
     * @var string
     */
    protected $schema;

    /**
     * Validation rules for FormRequest.
     *
     * @var string
     */
    protected $rules;

    /**
     * set default api.
     *
     * @var string
     */
    protected $api = true;

    protected $traits = [

    ];

    /**
     * The constructor.
     * @param $name
     * @param FileRepository $module
     * @param Config     $config
     * @param Filesystem $filesystem
     * @param Console    $console
     */
    public function __construct(
        $name,
        Config $config = null,
        Filesystem $filesystem = null,
        Console $console = null,
        Module  $module = null
    ) {
        $this->name = $name;
        $this->app = Container::getInstance();
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->module = $module;

        Stub::setBasePath( config('modules.paths.modules').'/Base/Console/stubs');
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the name of module will created. By default in studly case.
     *
     * @return string
     */
    public function getName()
    {
        return Str::studly($this->name);
    }

    /**
     * Get the laravel config instance.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the laravel config instance.
     *
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the laravel filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set the laravel filesystem instance.
     *
     * @param Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get the laravel console instance.
     *
     * @return Console
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Set the laravel console instance.
     *
     * @param Console $console
     *
     * @return $this
     */
    public function setConsole($console)
    {
        $this->console = $console;

        return $this;
    }

    public function setTraits($traits)
    {
        $this->traits = $traits;

        return $this;
    }

    /**
     * Get the Module instance.
     *
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set the Module instance.
     *
     * @param string $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = Module::find($module);

        return $this;
    }

    /**
     * Get the module instance.
     *
     * @return \Nwidart\Modules\Module
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the module instance.
     *
     * @param mixed $module
     *
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get the list of folders will created.
     *
     * @return array
     */
    public function getFolders()
    {
        return $this->config->get('base.paths.generator');
    }

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->config->get('base.stubs.files');
    }

    /**
     * Set force status.
     *
     * @param bool|int $force
     *
     * @return $this
     */
    public function setForce($force)
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Set schema.
     *
     * @param bool|int $force
     *
     * @return $this
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Set validation rules.
     *
     * @param bool|int $force
     *
     * @return $this
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        return new SchemaParser($this->schema);
    }

    /**
     * Get model fillables.
     *
     * @return array
     */
    public function getModelFillables()
    {
        return $this->getSchemaParser()->getFillables();
    }

    /**
     * Get model relationships.
     *
     * @return array
     */
    public function getModelRelationships()
    {
        return $this->getSchemaParser()->getRelationships();
    }

    /**
     * Get model headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->getSchemaParser()->getHeaderFormats();
    }

    /**
     * Get model input formats for form.
     *
     * @return array
     */
    public function getInputs()
    {
        return $this->getSchemaParser()->getInputFormats();
    }

    /**
     * Get model input formats for form.
     *
     * @return array
     */
    public function hasSoftDelete()
    {
        return $this->getSchemaParser()->hasSoftDelete();
    }

    /**
     * Generate the module.
     */
    public function generate() : int
    {
        $name = $this->getName();
        // dd( $this->config->get('base') );
        // dd( get_class($this->module), $name );
        // if ($this->module->has($name)) {
        //     if ($this->force) {
        //         $this->module->delete($name);
        //     } else {
        //         $this->console->error("Module [{$name}] already exist!");

        //         return E_ERROR;
        //     }
        // }

        $this->generateFolders();

        $this->generateResources();

        $this->generateFiles();

        $this->updateConfigFile();

        $this->updateRoutesStatuses();


        // $this->generateRouteJsonFile();
        // if ($this->type !== 'plain') {
        //     $this->generateFiles();
        //     $this->generateResources();
        // }
        // if ($this->type === 'plain') {
        //     $this->cleanModuleJsonFile();
        // }
        // $this->activator->setActiveByName($name, $this->isActive);

        $this->console->info("Route [{$name}] created successfully.");

        return 0;
    }

    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        foreach ($this->getFolders() as $key => $folder) {

            $folder = $this->generatorConfig($key);

            if ($folder->generate() === false) {
                continue;
            }

            $path = $this->module->getPath() . '/' . $folder->getPath();

            $path = $this->replaceString($path);

            if ($this->filesystem->exists($path) === true) {
                continue;
            }

            $this->filesystem->makeDirectory($path, 0755, true);

            if ( $this->config->get('base.stubs.gitkeep')) {
                $this->generateGitKeep($path);
            }
        }
    }

    /**
     * Generate git keep to the specified path.
     *
     * @param string $path
     */
    public function generateGitKeep($path)
    {
        $this->filesystem->put($path . '/.gitkeep', '');
    }

    /**
     * Generate the files.
     */
    public function generateFiles()
    {
        foreach ($this->getFiles() as $stub => $file) {

            $path = $this->module->getPath(). '/' . $file;

            $path = $this->replaceString($path);

            if (!$this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }


            $this->filesystem->put($path, $this->getStubContents($stub));

            $this->console->info("Created : {$path}");
        }
    }

    /**
     * Generate some resources.
     * TODO add make-route-request cmd with --rules
     */
    public function generateResources()
    {
        if($this->generatorConfig('route-controller')->generate()){
            $this->console->call('unusual:make:controller', [
                'name' => $this->getName(),
                'module' => $this->module->getStudlyName()
            ]);
        }

        if($this->generatorConfig('route-controller-api')->generate()){
            $this->console->call('unusual:make:controller:api', [
                'name' => $this->getName(),
                'module' => $this->module->getStudlyName()
            ]);
        }

        $console_traits =  $this->traits->mapWithKeys(function ($item, $key) {
            return ["--{$key}" => $item];
        })->toArray();

        $this->console->call('unusual:make:model', [
                'model' => $this->getName(),
                'module' => $this->module->getStudlyName()
            ]
            + ( count($this->getModelFillables()) ?  ['--fillable' => implode(",", $this->getModelFillables())] : [])
            + ( count($this->getModelRelationships()) ?  ['--relationships' => implode(",", $this->getModelRelationships())] : [])
            + ( $this->hasSoftDelete() ?  ['--soft-delete' => true] : [])
            + $console_traits
            + ['--notAsk' => true]
        );

        $this->console->call('unusual:make:migration', [
            'name' => "create_{$this->getDBTableName($this->name)}_table",
            'module' => $this->module->getStudlyName(),
        ] + ( $this->schema ?  ['--fields' => $this->schema] : [])
          + $console_traits
        );

        if($this->generatorConfig('repository')->generate()){
            // $this->console->call('module:make-repository', [
            $this->console->call('unusual:make:repository', [
                    'repository' => $this->getName(),
                    'module' => $this->module->getStudlyName()
                ]
                + $console_traits
                + ['--notAsk' => true]
            );
        }

        if($this->generatorConfig('route-request')->generate()){

            $this->console->call('unusual:make:request', [
                'request' => $this->getName(),
                'module' => $this->module->getStudlyName()
            ]
            + ( $this->rules ?  ['--rules' => $this->rules] : []) );
        }

        if($this->generatorConfig('route-resource')->generate()){
            $this->console->call('module:make-resource', [
                'name' => $this->getName().'Resource',
                'module' => $this->module->getStudlyName()
            ]);
        }



    }

    /**
     * updateConfigFile
     *
     * @return bool
     */
    public function updateRoutesStatuses()
    {
        $module = $this->app['ue_modules']->findOrFail($this->module);

        $module->setModuleActivator($this->module);

        $route = $this->getName();

        $module->enableRoute($route);

    }

    /**
     * updateConfigFile
     *
     * @return bool
     */
    public function updateConfigFile() :bool
    {
        $config = $this->getConfig()->get( $this->getModule()->getLowerName() );

        $lowerName = $this->getLowerNameReplacement();

        $studlyName = $this->getStudlyNameReplacement();

        $configPath = $this->module->getPath().'/Config/config.php';

        if( $this->getModule()->getName() === $this->getName()){
            $config['parent_route'] = [
                'name' => $studlyName,
                'url' => $lowerName,
                'route_name' => $lowerName,
                'table' => [
                    'headers' => $this->getHeaders(), //in OoBook\CRM\Base\Support\Migrations\SchemaParser::class
                    'inputs' => $this->getInputs() //in OoBook\CRM\Base\Support\Migrations\SchemaParser::class
                ]
            ];
        }else{
            $config['sub_routes'][$this->getLowerNameReplacement()] = [
                'name' => $studlyName,
                'url' => $lowerName,
                'route_name' => $lowerName,
                'table' => [
                    'headers' => $this->getHeaders(), //in OoBook\CRM\Base\Support\Migrations\SchemaParser::class
                    'inputs' => $this->getInputs() //in OoBook\CRM\Base\Support\Migrations\SchemaParser::class
                ]
            ];
        }

        return $this->filesystem->put($configPath, phpArrayFileContent($config));

    }

    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @param $stub
     *
     * @return string
     */
    protected function getStubContents($stub)
    {
        // dd( $stub, $this->getReplacement($stub) );
        return (new Stub(
            '/' . $stub . '.stub',
            $this->getReplacement($stub)
        )
        )->render();
    }

    /**
     * get the list for the replacements.
     */
    public function getReplacements()
    {
        return $this->config->get('base.stubs.replacements');
    }

    public function generatorConfig($generator)
    {
        return (new GeneratorPath($this->config->get('base.paths.generator.'.$generator)));
    }

    /**
     * Get array replacement for the specified stub.
     *
     * @param $stub
     *
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->getReplacements();

        if (!isset($replacements[$stub])) {
            return [];
        }

        $keys = $replacements[$stub];

        $replaces = [];

        if ($stub === 'json' || $stub === 'composer') {
            if (in_array('PROVIDER_NAMESPACE', $keys, true) === false) {
                $keys[] = 'PROVIDER_NAMESPACE';
            }
        }
        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get' . ucfirst(Str::studly(strtolower($key))) . 'Replacement')) {
                $replaces[$key] = $this->$method();
            } else {
                $replaces[$key] = null;
            }
        }

        return $replaces;
    }

    public function replaceString($string)
    {
        $patterns = [
            '/\$LOWER_NAME\$/' => $this->getLowerNameReplacement(),
            '/\$STUDLY_NAME\$/' => $this->getStudlyNameReplacement(),
        ];

        return preg_replace( array_keys($patterns), array_values($patterns), $string);

    }

    /**
     * Generate the module.json file
     */
    private function generateRouteJsonFile()
    {
        // dd($this->module->getPath());

        $path = $this->module->getPath(). '/module-routes.json';
        // $path = $this->module->getModulePath($this->getName()) . 'module.json';

        // if (!$this->filesystem->isDirectory($dir = dirname($path))) {
        //     $this->filesystem->makeDirectory($dir, 0775, true);
        // }
        dd( $this->getStubContents('json') );
        $this->filesystem->put($path, $this->getStubContents('json'));

        $this->console->info("Created : {$path}");
    }

    /**
     * Remove the default service provider that was added in the module.json file
     * This is needed when a --plain module was created
     */
    private function cleanModuleJsonFile()
    {
        $path = $this->module->getModulePath($this->getName()) . 'module.json';

        $content = $this->filesystem->get($path);
        $namespace = $this->getModuleNamespaceReplacement();
        $studlyName = $this->getStudlyNameReplacement();

        $provider = '"' . $namespace . '\\\\' . $studlyName . '\\\\Providers\\\\' . $studlyName . 'ServiceProvider"';

        $content = str_replace($provider, '', $content);

        $this->filesystem->put($path, $content);
    }

    /**
     * Get the module name in lower case.
     *
     * @return string
     */
    protected function getLowerNameReplacement()
    {
        return $this->getLowerName($this->getName());
    }

    /**
     * Get the module name in studly case.
     *
     * @return string
     */
    protected function getStudlyNameReplacement()
    {
        return $this->getName();
    }

    /**
     * Get replacement for $VENDOR$.
     *
     * @return string
     */
    protected function getVendorReplacement()
    {
        return config('modules.composer.vendor');
    }

    /**
     * Get replacement for $MODULE_NAMESPACE$.
     *
     * @return string
     */
    protected function getModuleNamespaceReplacement()
    {
        return str_replace('\\', '\\\\', config('modules.namespace'));
    }

    /**
     * Get replacement for $AUTHOR_NAME$.
     *
     * @return string
     */
    protected function getAuthorNameReplacement()
    {
        return config('modules.composer.author.name');
    }

    /**
     * Get replacement for $AUTHOR_EMAIL$.
     *
     * @return string
     */
    protected function getAuthorEmailReplacement()
    {
        return config('modules.composer.author.email');
    }

}
