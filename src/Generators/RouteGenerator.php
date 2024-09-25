<?php

namespace Unusualify\Modularity\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Unusualify\Modularity\Support\Decomposers\SchemaParser;
use Unusualify\Modularity\Traits\ManageNames;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Nwidart\Modules\Support\Stub;
use Illuminate\Container\Container;
use Modules\SystemUser\Repositories\PermissionRepository;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;

use function Laravel\Prompts\{confirm};


class RouteGenerator extends Generator
{
    use ManageNames;

    /**
     * The route name to be created
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
     * @var \Unusualify\Modularity\Module
     */
    protected $module;

    /**
     * The module name
     *
     * @var string
     */
    protected $moduleName;

    /**
     * The route name.
     *
     * @var string
     */
    protected $route;

    /**
     * Force status.
     *
     * @var bool
     */
    protected $force = false;

    /**
     * Migration status.
     *
     * @var bool
     */
    protected $migrate = true;

    /**
     * Migration status.
     *
     * @var bool
     */
    protected $migration = true;

    /**
     * Plain Status.
     *
     * @var bool
     */
    protected $plain = false;

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
     * Model relationships.
     *
     * @var string
     */
    protected $relationships;

    /**
     * use default inputs and headers
     *
     * @var boolean
     */
    protected $useDefaults;

    /**
     * The custom model is already defined in project directory or third party model
     *
     * @var boolean
     */
    protected $customModel;

    /**
     * set default api.
     *
     * @var string
     */
    protected $api = true;



    protected $fix = false;


    /**
     * modelRelationParser
     *
     * @var Unusualify\Modularity\Support\Decomposers\ModelRelationParser::class
     */
    protected $modelRelationParser;


    protected $traits = [

    ];

    protected static $defaultTableOptions = [
        'createOnModal' => true,
        'editOnModal' => true,
        'isRowEditing' => false,
        'rowActionsType' => 'inline',
    ];

    protected $tableName;

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
        Module $module = null
    ) {
        $this->name = $name;
        $this->app = Container::getInstance();
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->module = $module;

        $this->moduleName = $this->module ? $this->module->getName() : null;


        // Stub::setBasePath( config('modules.paths.modules').'/Base/Console/stubs');
    }

    /**
     * Set the fix attribute
     *
     * @param boolean|int $fix
     *
     * @return $this
     */
    public function setFix($fix){
        $this->fix = $fix;

        return $this;
    }

    /**
     * Get if the configuration is set as fix or not
     *
     * @return boolean|int
     */
    public function getFix(){
        return $this->fix;
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
        $modularity = App::makeWith(\Unusualify\Modularity\Modularity::class, ['app' => $this->app]);

        $this->module = $modularity->find($module);

        $this->moduleName = $this->module->getName();

        // if($this->module == null){
        //     dd(
        //         $modularity->findNotCached($module),
        //         array_keys($modularity->scan()),
        //         array_keys($modularity->all()),
        //     );
        // }

        return $this;
    }

    /**
     * Get the module instance.
     *
     * @return string
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
        return $this->config->get(unusualBaseKey() . '.paths.generator');
    }

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->config->get(unusualBaseKey() . '.stubs.files');
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
     * Set migrate status.
     *
     * @param bool|int $notMigrate
     *
     * @return $this
     */
    public function setMigrate($notMigrate)
    {
        $this->migrate = !$notMigrate;

        return $this;
    }

    /**
     * Set migrate status.
     *
     * @param bool|int $notMigrate
     *
     * @return $this
     */
    public function setMigration($notMigration)
    {
        $this->migration = !$notMigration;

        return $this;
    }

    /**
     * Set useDefault.
     *
     * @param bool|int $noDefault
     *
     * @return $this
     */
    public function setUseDefaults($noDefaults)
    {
        $this->useDefaults = !$noDefaults;

        return $this;
    }

    /**
     * Set plain status.
     *
     * @param bool|int $force
     *
     * @return $this
     */
    public function setPlain($plain)
    {
        $this->plain = $plain;

        return $this;
    }

    /**
     * Set schema.
     *
     * @param bool|int $force
     *
     * @example name:string:unique,type:enum('type'\,['POS'\,'SERVICE'])
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
     * @param bool|int $rules
     *
     * @example name=required|min:3|unique:payment
     *
     * @return $this
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Set model relationships.
     *
     * @param string $relationships
     *
     * @return $this
     */
    public function setRelationships($relationships)
    {
        $this->relationships = $relationships;

        return $this;
    }

    /**
     * Set custom model.
     *
     * @param string class
     *
     * @return $this
     */
    public function setCustomModel($class)
    {
        if( @class_exists($class) )
            $this->customModel = $class;

        return $this;
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        return new SchemaParser($this->schema, $this->useDefaults);
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
        $additional = [];
        foreach(explode('|', $this->relationships) as $relationship){
            $additional = array_merge($additional, App::makeWith(SchemaParser::class, [
                'schema' => $relationship,
                'useDefaults' => $this->useDefaults,
                'model' => $this->getName(),
            ])->getRelationships());
        }

        return array_merge(
            $this->getSchemaParser()->getRelationships(),
            $additional
        );

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

        if($this->getTest()){
            $this->runTest();

            return 0;
        }else {
            if ($this->module->getRouteConfig($name)) {
                // dd($this->force);
                if ($this->force) {
                    // $this->module->delete($name);
                } else if(!$this->fix){
                    $this->console->error("Module Route [{$name}] already exist!");

                    return E_ERROR;
                }
            }

            if($this->fix){
                $this->fixConfigFile();
            }else{
                $this->updateConfigFile();
            }


            $this->addLanguageVariable();

            if(!$this->plain){

                $this->updateRoutesStatuses();

                $this->generateFolders();

                $this->generateResources();

                $this->generateFiles();

                $this->createRoutePermissions();

                if($this->migrate && !$this->fix){ //!$this->module->isRouteTableExists($name)

                    $this->console->call('unusual:migrate', [
                        'module' => $this->module->getStudlyName()
                    ]);

                    $this->console->info("Migration of [{$name}] run.");
                }
            }
        }


        // $this->generateRouteJsonFile();
        // if ($this->type !== 'plain') {
        //     $this->generateFiles();
        //     $this->generateResources();
        // }
        // if ($this->type === 'plain') {
        //     $this->cleanModuleJsonFile();
        // }
        // $this->activator->setActiveByName($name, $this->isActive);

        $this->console->info("Route [{$name}] " .($this->fix ? 'fixed' : 'created'). " successfully.");

        return 0;
    }

    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        $runnable = (!$this->getTest() || ($confirmed = confirm(label: 'Do you want to test the folders to be created?', default: false)) );

        if( $runnable ){
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

                if($this->getTest()){
                    $this->console->info("It's going to create {$path} directory!");
                }else{
                    $this->filesystem->makeDirectory($path, 0755, true);

                    if ( $this->config->get(unusualBaseKey() . '.stubs.gitkeep')) {
                        $this->generateGitKeep($path);
                    }
                }

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

            if(!file_exists($path)){

                $this->filesystem->put($path, $this->getStubContents($stub));

                $this->console->info("Created : {$path}");
            }
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
                'module' => $this->module->getStudlyName(),
                'name' => $this->getName(),
            ]);
        }

        if($this->generatorConfig('route-controller-api')->generate()){
            $this->console->call('unusual:make:controller:api', [
                'module' => $this->module->getStudlyName(),
                'name' => $this->getName(),
            ]);
        }

        if($this->generatorConfig('route-controller-front')->generate()){
            $this->console->call('unusual:make:controller:front', [
                'module' => $this->module->getStudlyName(),
                'name' => $this->getName(),
            ]);
        }

        $console_traits =  $this->traits->mapWithKeys(function ($item, $key) {
            return ["--{$key}" => $item];
        })->toArray();

        $hasCustomModel = $this->customModel && @class_exists($this->customModel);

        $this->console->call('unusual:make:model', [
                'module' => $this->module->getStudlyName(),
                'model' => $this->getName()
            ]
            + ( count($this->getModelFillables()) ?  ['--fillable' => implode(",", $this->getModelFillables())] : [])
            + ( count($this->getModelRelationships()) ?  ['--relationships' => implode("|", $this->getModelRelationships())] : [])
            + ( $this->hasSoftDelete() ?  ['--soft-delete' => true] : [])
            + ( $hasCustomModel ?  ['--override-model' => $this->customModel] : [])
            + $console_traits
            + ['--notAsk' => true]
            + ( !$this->useDefaults ?  ['--no-defaults' => true] : [])
        );

        $tableName = $this->getTableName() ? $this->getTableName() : $this->getDBTableName($this->name);

        if (!$hasCustomModel) {
            if (!$this->module->isFileExists("create_{$tableName}_table") && !$this->fix) {
                $this->console->call(
                    'unusual:make:migration',
                    [
                        'module' => $this->module->getStudlyName(),
                        'name' => "create_{$tableName}_table",
                    ]
                        + ($this->schema ?  ['--fields' => $this->schema] : [])
                        + (!$this->useDefaults ?  ['--no-defaults' => true] : [])
                        + $console_traits
                        + ['--test' => $this->getTest()]
                );
            }
        } else if($this->migration){
            if (!$this->module->isFileExists("add_{$tableName}_table") && !$this->fix) {
                $this->console->call(
                    'unusual:make:migration',
                    [
                        'module' => $this->module->getStudlyName(),
                        'name' => "add_{$tableName}_table",
                    ]
                    + ($this->schema ?  ['--fields' => $this->schema] : [])
                    + (!$this->useDefaults ?  ['--no-defaults' => true] : [])
                    + $console_traits
                    + ['--table-name' => $tableName]
                    + ['--test' => $this->getTest()]
                );
            }
        }

        $this->generateExtraMigrations();

        if($this->generatorConfig('repository')->generate()){
            // $this->console->call('module:make-repository', [
            $this->console->call('unusual:make:repository', [
                'module' => $this->module->getStudlyName(),
                'repository' => $this->getName()
                ]
                // + ($hasCustomModel ? [ '--custom-model' => $this->customModel] : [])
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
     * updateRoutesStatuses
     *
     * @return bool
     */
    public function updateRoutesStatuses()
    {
        $module = Modularity::findOrFail($this->module);

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

        $config = $this->getConfig()->get( $this->getModule()->getSnakeName()) ?? [];

        $headline = $this->getHeadline($this->getName());
        $studlyName = $this->getStudlyNameReplacement();
        $kebabCase = $this->getKebabCase($this->getName());
        $snakeCase = $this->getSnakeCase($this->getName());
        $lowerCase = lowerName($this->getName());
        // $configPath = $this->module->getPath().'/Config/config.php';
        $configPath = $this->module->getConfigPath();

        if($this->getModule()->getName() === $this->getName()){

            $config['name'] =  $config['name'] ?? $studlyName;
            $config['system_prefix'] = $config['system_prefix'] ?? $config['base_prefix'] ?? false;
            $config['headline'] = $config['headline'] ?? pluralize($headline);
            // $config['parent_route'] = $route_array;
            $config['routes'] = $config['routes'] ?? [];

        }


        $runnable = (!$this->getTest() || ($confirmed = confirm(label: 'Do you want to test the config file?', default: false)) );

        if(!$this->plain){

            $headers = $this->getHeaders();

            $titleColumnKey = count($filtered = array_filter($headers, fn($i) => $i['key'] === 'name' || $i['key'] === 'title')) > 0
                ? $filtered[0]['key']
                : $headers[0]['key'];

            $route_array = ($this->getModule()->getName() === $this->getName() ? ['parent' => true] : []) + [
                'name' =>  $studlyName,
                'headline' => pluralize($headline),
                'url' => pluralize($kebabCase),
                'route_name' => $snakeCase,
                'icon' => '', //'$modules',
                'title_column_key' => $titleColumnKey,
                'table_options' => static::$defaultTableOptions,
                'headers' => $headers, //in Unusualify\Modularity\Support\Migrations\SchemaParser::class
                'inputs' => $this->getInputs() //in Unusualify\Modularity\Support\Migrations\SchemaParser::class
            ];

            if($runnable && $this->getTest()){
                dump($route_array);
            }

            $config['routes'][$this->getSnakeCase($this->getName())] = $route_array;
        }

        $content = $this->filesystem->exists($configPath)
            ? add_route_to_config($configPath, $this->getName(), $route_array)
            : php_array_file_content($config);

        return $this->getTest()
            ? 1
            : $this->filesystem->put( $configPath, $content);

    }

    public function fixConfigFile()
    {

        if($this->fix){
            $configPath = $this->module->getConfigPath();
            $config = $this->getConfig()->get( $this->getModule()->getSnakeName()) ?? [];
            $moduleName = $this->getModule()->getName();
            $routeName = $this->getName();
            $routeArray = $config['routes'][$this->getSnakeCase($routeName)] ?? [];

            empty($config['name']) ? ($config['name'] = $this->getHeadline($moduleName)) : null;
            empty($config['system_prefix']) ? $config['system_prefix'] = $config['base_prefix'] ?? false : null;
            empty($config['headline']) ? $config['headline'] =  pluralize($this->getHeadline($moduleName)) : null;

            $route_array = ($this->getModule()->getName() === $this->getName() ? ['parent' => true] : []) + [
                'name' => getValueOrNull($routeArray,key:'name') ?? $routeName,
                'headline' => getValueOrNull($routeArray,key:'headline') ??  pluralize($this->getHeadline($routeName)),
                'url' => getValueOrNull($routeArray,key:'url') ??  pluralize($this->getKebabCase($routeName)),
                'route_name' => getValueOrNull($routeArray,key:'route_name') ?? $this->getSnakeCase($routeName),
                'icon' => getValueOrNull($routeArray,key:'icon') ?? '',
                'table_options' => getValueOrNull($routeArray,key:'table_options') ?? static::$defaultTableOptions,
                'headers' => $routeArray['headers'] ?? $this->getHeaders(),
                'inputs' => $routeArray['inputs'] ?? $this->getInputs(),
            ];



            $config['routes'][$this->getSnakeCase($this->getName())] = array_merge($config['routes'][$this->getSnakeCase($this->getName())], $route_array);


            uksort($config, fn($a) => is_string($config[$a]) ?  -1 : (is_bool($config[$a]) ?  0 : 1));
            $this->module->setConfig($config);
            return $this->filesystem->put($configPath, php_array_file_content($config));
        }



    }

    /**
     * addLanguageVariable
     *
     * @return bool
     */
    public function addLanguageVariable() :bool
    {
        $headline = $this->getHeadline($this->getName());
        $plural = pluralize($headline);

        $langDir = $this->module->getDirectoryPath(GenerateConfigReader::read('lang')->getPath());

        foreach (getLocales() as $locale) {
            $file = $langDir . "/{$locale}/modules.php";

            if($this->filesystem->exists($file)){
                $lang = include($file);

                if(!isset($lang[$this->getSnakeCase($this->name)])){
                    $lang[$this->getSnakeCase($this->name)] = "{$headline} | {$plural} | {n} {$plural}";
                    $this->filesystem->put($file, php_array_file_content($lang));
                }
            }else{
                $lang = [
                    $this->getSnakeCase($this->name) => "{$headline} | {$plural} | {n} {$plural}"
                ];

                if (!$this->filesystem->isDirectory($dir = dirname($file))) {
                    $this->filesystem->makeDirectory($dir, 0777, true);
                }

                $this->filesystem->put($file, php_array_file_content($lang));
            }
        }

        // foreach(glob( base_path('lang') . "/**/modules.php") as $path) {
        //     $lang = include($path);

        //     if(!isset($lang[$this->getSnakeCase($this->name)])){
        //         $lang[$this->getSnakeCase($this->name)] = "{$headline} | {$plural} | {n} {$plural}";
        //         $this->filesystem->put($path, php_array_file_content($lang));
        //     }

        // }

        // foreach (glob(__DIR__ . "/../../lang/*.json") as $filename) {
        //     $arr = json_decode( file_get_contents($filename), true );
        //     $arr['modules'][$this->getSnakeCase($this->name)] = "{$headline} | {$plural} | {n} {$plural}";
        //     file_put_contents($filename, collect($arr)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // }
        // \Illuminate\Support\Facades\File::get( __DIR__ . '/');
        return true;
    }

    /**
     * updateConfigFile
     *
     * @return bool
     */
    public function createRoutePermissions() :bool
    {
        $kebabCase = $this->getKebabCase($this->getName());

        $repository = App::make(PermissionRepository::class);

        // default permissions of a module
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::CREATE->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::VIEW->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::EDIT->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::DELETE->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::FORCEDELETE->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::RESTORE->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::DUPLICATE->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::REORDER->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::BULK->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::BULKDELETE->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::BULKFORCEDELETE->value, 'guard_name' => 'unusual_users']);
        $repository->firstOrCreate(['name' => $kebabCase . "_" . Permission::BULKRESTORE->value, 'guard_name' => 'unusual_users']);

        return true;
    }

    /**
     *
     *
     * @return bool
     */
    public function generateExtraMigrations() :bool
    {

        if($this->relationships !== ''){

            foreach(explode('|', $this->relationships) as $schema){
                $parser = App::makeWith(SchemaParser::class, [
                    'schema' => $schema
                ]);

                $route_name = '';

                foreach($parser->getColumnTypes() as $column => $type){
                    if(in_array($type, ['belongsToMany'])){
                        // dd(explode('|', $this->relationships), $parser->getColumnTypes());
                        $route_name = $column;
                        sleep(1);

                        $pivot_table_name = snakeCase($this->name) . '_' . snakeCase($route_name);
                        if(!$this->module->isFileExists("create_{$pivot_table_name}_table")){
                            $this->console->call('unusual:make:migration', [
                                '--relational' => 'BelongsToMany',
                                '--no-defaults' => true,
                                '--route' => $this->name,
                                'module' => $this->module->getStudlyName(),
                                'name' => "create_{$pivot_table_name}_table",
                                ]
                                + ( $schema ?  ['--fields' => $schema] : [])
                            );
                        }
                        continue;
                    }

                    if(in_array($type, ['morphedByMany']) || in_array($column, ['morphedByMany'])){
                        // $migratable = true;
                        $route_name = in_array($column, ['morphedByMany']) ? $this->name : $column;

                        sleep(1);
                        $pivot_table_name = $this->getMorphPivotTableName($route_name);

                        if(!$this->module->isFileExists("create_{$pivot_table_name}_table")){

                            $this->console->call('unusual:make:migration', [
                                '--relational' => 'MorphedByMany',
                                '--no-defaults' => true,
                                'module' => $this->module->getStudlyName(),
                                'name' => "create_{$pivot_table_name}_table",
                                ]
                                + ( $this->schema ?  ['--fields' => $schema] : [])
                            );
                        }
                        break;
                    }
                }

            }


        }

        return true;
    }

    public function generatorConfig($generator)
    {
        return (new GeneratorPath($this->config->get(unusualBaseKey() . '.paths.generator.'.$generator)));
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
        return (new Stub(
            '/' . $stub . '.stub',
            $this->getReplacement($stub)
        )
        )->render();
    }

    /**
     * Get the list for the replacements.
     */
    public function getReplacements()
    {
        return $this->config->get(unusualBaseKey() . '.stubs.replacements');
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
            '/\$KEBAB_CASE\$/' => $this->getKebabCase($this->getName()),
            '/\$PASCAL_CASE\$/' => $this->getPascalCase($this->getName()),
            '/\$SNAKE_CASE\$/' => $this->getSnakeCase($this->getName()),
            '/\$CAMEL_CASE\$/' => $this->getCamelCase($this->getName()),
        ];

        return preg_replace( array_keys($patterns), array_values($patterns), $string);

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
     * Get the route name in lower case.
     *
     * @return string
     */
    protected function getLowerNameReplacement()
    {
        return $this->getLowerName($this->getName());
    }

    /**
     * Get the module name in lower case.
     *
     * @return string
     */
    protected function getModuleLowerNameReplacement()
    {
        return $this->getLowerName($this->module->getName());
    }
    protected function getLowerModuleNameReplacement()
    {
        return $this->getLowerName($this->module->getName());
    }

    /**
     * Get the module name in studly case.
     *
     * @return string
     */
    protected function getModuleStudlyNameReplacement()
    {
        return $this->module->getName();
    }

    protected function getStudlyModuleNameReplacement()
    {
        return $this->module->getName();
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
        return unusualConfig('composer.vendor');
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
     * Get replacement for $AUTHOR$.
     *
     * @return string
     */
    protected function getAuthorReplacement()
    {
        return unusualConfig('composer.author.name');
    }

    /**
     * Get replacement for $AUTHOR_EMAIL$.
     *
     * @return string
     */
    protected function getAuthorEmailReplacement()
    {
        return unusualConfig('composer.author.email');
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    protected function runTest()
    {
        if(!$this->plain){

            $this->updateConfigFile();

            // $this->generateFolders();

            $console_traits =  $this->traits->mapWithKeys(function ($item, $key) {
                return ["--{$key}" => $item];
            })->toArray();

            $hasCustomModel = $this->customModel && @class_exists($this->customModel);

            $this->console->call('unusual:make:model', [
                    'module' => $this->module->getStudlyName(),
                    'model' => $this->getName()
                ]
                + ( count($this->getModelFillables()) ?  ['--fillable' => implode(",", $this->getModelFillables())] : [])
                + ( count($this->getModelRelationships()) ?  ['--relationships' => implode("|", $this->getModelRelationships())] : [])
                + ( $this->hasSoftDelete() ?  ['--soft-delete' => true] : [])
                + ( $hasCustomModel ?  ['--override-model' => $this->customModel] : [])
                + $console_traits
                + ['--notAsk' => true]
                + ( !$this->useDefaults ?  ['--no-defaults' => true] : [])
                + ['--test' => $this->getTest()]
            );

            $tableName = $this->getTableName() ? $this->getTableName() : $this->getDBTableName($this->name);

            if(!$hasCustomModel) {
                if(!$this->module->isFileExists("create_{$tableName}_table") && !$this->fix){
                    $this->console->call('unusual:make:migration', [
                            'module' => $this->module->getStudlyName(),
                            'name' => "create_{$tableName}_table",
                        ]
                        + ( $this->schema ?  ['--fields' => $this->schema] : [])
                        + ( !$this->useDefaults ?  ['--no-defaults' => true] : [])
                        + $console_traits
                        + ['--table-name' => $tableName]
                        + ['--test' => $this->getTest()]
                    );
                }
            }else if($this->migration){
                if (!$this->module->isFileExists("add_{$tableName}_table") && !$this->fix) {
                    $this->console->call(
                        'unusual:make:migration',
                        [
                            'module' => $this->module->getStudlyName(),
                            'name' => "add_{$tableName}_table",
                        ]
                        + ($this->schema ?  ['--fields' => $this->schema] : [])
                        + (!$this->useDefaults ?  ['--no-defaults' => true] : [])
                        + $console_traits
                        + ['--table-name' => $tableName]
                        + ['--test' => $this->getTest()]
                    );
                }
            }
            // dd( 'end of test');

            // $this->generateFiles();
        }

        $this->console->info('Route generator test is completed successfully!');

        return 0;
    }

}
