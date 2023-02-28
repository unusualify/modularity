<?php

namespace Unusual\CRM\Base\Console;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Unusual\CRM\Base\Support\Decomposers\SchemaParser;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Migrations\NameParser;
// use Nwidart\Modules\Support\Migrations\SchemaParser;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationMakeCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'unusual:make:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration for the specified module.';

    protected $defaultFieldSchemas = [
        "title:string('title'\,200):nullable",
        "description:text:nullable",
    ];


    /**
     * Run the command.
     */
    public function handle() : int
    {
        $this->defaultFieldSchemas += $this->baseConfig('schemas.default_fields') ?? [];

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        $this->info('Migration created successfully!');


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
            ['name', InputArgument::REQUIRED, 'The migration name will be created.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be created.'],
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
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
            ['plain', null, InputOption::VALUE_NONE, 'Create plain migration.'],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
            // ['translationTrait', '--T', InputOption::VALUE_NONE, 'Whether model has translation trait or not'],
            // ['positionTrait', '--P', InputOption::VALUE_NONE, 'Whether model has position trait or not'],
            // ['mediasTrait', '--M', InputOption::VALUE_NONE, 'Whether model has media trait or not'],
            // ['filesTrait', '--F', InputOption::VALUE_NONE, 'Whether model has file trait or not'],
        ] + unusualTraitOptions();
    }



    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $parser = new NameParser($this->argument('name'));

        if ($parser->isCreate()) {
            return Stub::create('/migration/create.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields'        => ltrim($this->getSchemaParser()->render()),
                'up_schemas'    => ltrim($this->getExtraUpSchemaMethods()),
                'down_schemas'  => ltrim($this->getExtraDownSchemaMethods()),
            ]);
        } elseif ($parser->isAdd()) {
            return Stub::create('/migration/add.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields_up'     => $this->getSchemaParser()->up(),
                'fields_down'   => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDelete()) {
            return Stub::create('/migration/delete.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields_down'   => $this->getSchemaParser()->up(),
                'fields_up'     => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDrop()) {
            return Stub::create('/migration/drop.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields'        => $this->getSchemaParser()->render(),
            ]);
        }

        return Stub::create('/migration/plain.stub', [
            'class' => $this->getClass(),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $generatorPath = GenerateConfigReader::read('migration');

        return $path . $generatorPath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {

        $fields = '';

        if($this->option('positionTrait')){
            $fields .= "position:integer:unsigned:nullable,";
        }

        if(!$this->option('translationTrait')){
            $fields .= implode(",", $this->defaultFieldSchemas).",";
            $fields .= $this->option('fields');
        }

        return new SchemaParser(rtrim($fields, ","));
    }

    public function getExtraUpSchemaMethods()
    {
        $schemas = "";
        $singular_table = Str::singular( (new NameParser($this->argument('name')))->getTableName() );

        if($this->option('translationTrait')){
            $fields = implode(",", $this->defaultFieldSchemas).","
                .$this->option('fields');

            $schemas .= "\t\t\tSchema::create('{$singular_table}_translations', function(Blueprint \$table) {\n"
                ."\t\t\tcreateDefaultTranslationsTableFields(\$table, '{$singular_table}');\n"
                // . (new SchemaParser(implode(",", $this->defaultFieldSchemas)))->render()
                . (new SchemaParser(rtrim($fields)))->render()
                ."\t\t});\n\n";
        }

        return $schemas;
    }

    public function getExtraDownSchemaMethods()
    {
        $schemas = "";

        $table = (new NameParser($this->argument('name')))->getTableName();

        $singular_table = Str::singular( $table );

        if($this->option('translationTrait')){
            // $results = "\t\t\t" . '$table';
            $schemas .= "\t\t\tSchema::dropIfExists('{$singular_table}_translations');\n";
        }

        $schemas .= "\t\tSchema::dropIfExists('{$table}');";

        return $schemas;
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return date('Y_m_d_His_') . $this->getSchemaName();
    }

    /**
     * @return array|string
     */
    private function getSchemaName()
    {
        return $this->argument('name');
    }

    /**
     * @return string
     */
    private function getClassName()
    {
        return Str::studly($this->argument('name'));
    }

    public function getClass()
    {
        return $this->getClassName();
    }


}
