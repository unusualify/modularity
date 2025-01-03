<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\Config;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Unusualify\Modularity\Support\Decomposers\SchemaParser;
use Unusualify\Modularity\Traits\ManageNames;

use function Laravel\Prompts\confirm;

class BaseCommand extends GeneratorCommand
{
    use ManageNames, ModuleCommandTrait;

    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';

    protected $isAskable = true;

    protected $responses = [];

    protected $defaultReject = false;

    protected $configBaseKey = 'base';

    protected $schemaParser;

    protected $test = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->configBaseKey = \Illuminate\Support\Str::snake(env('MODULARITY_BASE_NAME', 'Modularity'));

        Stub::setBasePath($this->baseConfig('stubs.path', dirname(__FILE__) . '/stubs'));
    }

    public function baseConfig($string, $default = null)
    {
        return Config::get($this->configBaseKey . '.' . $string, $default);
    }

    /**
     * Get template contents.
     *
     * @return string
     */
    protected function getTemplateContents() {}

    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath() {}

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePath());
        // dd(
        //     // get_class_methods($this),
        //     // $this->getDefinition()
        //     // $this->getSynopsis(true),
        //     // $this->getSynopsis(),
        //     // $this->getUsages(),
        //     // strtolower($this->getDescription())
        // );
        $description = trim(mb_strtolower($this->getDescription()), '.');

        $runnable = ! $this->hasOption('test')
            || ! $this->option('test')
            || ($confirmed = confirm(label: "Do you want to {$description} in test mode?", default: false));

        if ($runnable) {
            if ($this->hasOption('test') && $this->option('test')) {
                $contents = $this->getTemplateContents();

                $this->info($contents);
            } else {
                if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
                    $this->laravel['files']->makeDirectory($dir, 0777, true);
                }

                $contents = $this->getTemplateContents();

                try {
                    $this->components->task("Generating file {$path}", function () use ($path, $contents) {
                        $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
                        (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();
                    });

                } catch (FileAlreadyExistException $e) {
                    $this->components->error("File : {$path} already exists.");

                    return E_ERROR;
                }
            }
        }

        return 0;
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        return $this->schemaParser ?? ($this->schemaParser = new SchemaParser);
    }

    protected function setAskability()
    {
        if ($this->hasOption('notAsk')) {
            $this->isAskable = false;
        }
    }

    protected function isAskable()
    {
        return $this->isAskable ?? true;
    }

    protected function getTraitResponse($trait): bool
    {
        return array_key_exists($trait, $this->responses) ? $this->responses[$trait] : false;
    }

    protected function setTest($test)
    {
        $this->test = $test;

        return $this;
    }

    protected function getTest()
    {
        return $this->test;
    }
}
