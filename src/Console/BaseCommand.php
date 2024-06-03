<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Support\Decomposers\SchemaParser;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Unusualify\Modularity\Traits\ManageNames;

class BaseCommand extends GeneratorCommand
{
    use ModuleCommandTrait, ManageNames;

    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';

    protected $isAskable = true;

    protected $responses = [];

    protected $defaultReject = false;

    protected $configBaseKey = "base";

    protected $schemaParser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->configBaseKey = \Illuminate\Support\Str::snake(env('MODULARITY_BASE_NAME', 'Modularity'));

        Stub::setBasePath( $this->baseConfig('stubs.path', dirname(__FILE__).'/stubs') );
    }

    public function baseConfig($string, $default = null){
        return Config::get( $this->configBaseKey .".".$string, $default);
    }

    /**
     * Get template contents.
     *
     * @return string
     */
    protected function getTemplateContents(){

    }

    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath(){

    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        return $this->schemaParser ?? ($this->schemaParser = new SchemaParser());
    }

    protected function setAskability()
    {
        if($this->hasOption('notAsk'))
            $this->isAskable = false;
    }

    protected function isAskable()
    {
        return $this->isAskable ?? true;
    }

    protected function getTraitResponse($trait) : bool
    {
        return array_key_exists($trait, $this->responses) ? $this->responses[$trait] : false;
    }
}
