<?php

namespace OoBook\CRM\Base\Console;

use Illuminate\Support\Facades\Config;
use OoBook\CRM\Base\Support\Decomposers\SchemaParser;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use OoBook\CRM\Base\Traits\Namable;

class BaseCommand extends GeneratorCommand
{
    use ModuleCommandTrait, Namable;

    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';

    protected $baseModule;

    protected $isAskable = true;

    protected $responses = [];

    protected $defaultReject = false;

    protected $config_base = "base";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // $this->baseModule = Module::find('Base');

        Stub::setBasePath( $this->baseConfig('stubs.path', dirname(__FILE__).'/stubs') );
    }

    public function baseConfig($string, $default = null){
        return Config::get( lowerName( env('BASE_NAME','Base') ) .".".$string, $default);
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
