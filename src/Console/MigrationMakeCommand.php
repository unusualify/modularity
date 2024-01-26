<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Unusualify\Modularity\Support\Decomposers\SchemaParser;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Migrations\NameParser;
use Nwidart\Modules\Support\Migrations\SchemaParser as NwidartSchemaParser;
// use Nwidart\Modules\Support\Migrations\SchemaParser;

use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Facades\Modularity;

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

    protected $defaultFieldSchemas = [];


    /**
     * Run the command.
     */
    public function handle() : int
    {
        if(!$this->option('no-defaults')){
            $this->defaultFieldSchemas = $this->baseConfig('schemas.default_fields') ?? [];
        }

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        if($this->option('relational')){
            $this->info('Relational (Pivot) Migration created successfully!');
        }else {
            $this->info('Migration created successfully!');
        }


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
            ['module', InputArgument::REQUIRED, 'The name of module that the migration will be created in.'],
            ['name', InputArgument::REQUIRED, 'The migration name will be created.'],
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
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
            ['plain', null, InputOption::VALUE_NONE, 'Create plain migration.'],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['relational', null, InputOption::VALUE_NONE, 'Create relational table for many-to-many and polymorphic relationships.'],
            ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            ['no-defaults', null, InputOption::VALUE_NONE, 'unuse default input and headers.'],
            ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
        ], unusualTraitOptions());
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $parser = new NameParser($this->argument('name'));

        if($this->option('relational')) {
            $table_name = $parser->getTableName();
            $table1 = $this->getSnakeCase($this->argument('module'));
            $table2 = preg_replace("/^({$table1}_)(\w+)$/", '${2}', $table_name);

            return Stub::create('/migration/pivot.stub', [
                'class'         => $this->getClass(),
                'table'         => $table_name,
                'fields'        => ltrim((new SchemaParser(useDefaults:false))->render()),

                'table1'        => $table1,
                'table2'        => $table2,
            ]);
        } elseif ($parser->isCreate()) {
            return Stub::create('/migration/create.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields'        => ltrim($this->getSchemaParser()->render()),
                'up_schemas'    => ltrim($this->getExtraUpSchemaMethods()),
                'down_schemas'  => ltrim($this->getExtraDownSchemaMethods()),
            ]);
        } elseif ($parser->isAdd()) {
            $schemaParser = new NwidartSchemaParser($this->option('fields'));
            return Stub::create('/migration/add.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields_up'     => ltrim(rtrim($schemaParser->up())),
                'fields_down'   => ltrim(rtrim($schemaParser->down())),
            ]);
        } elseif ($parser->isDelete()) {
            $schemaParser = new NwidartSchemaParser($this->option('fields'));
            return Stub::create('/migration/delete.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields_down'   => $schemaParser->up(),
                'fields_up'     => $schemaParser->down(),
            ]);
        } elseif ($parser->isDrop()) {
            $schemaParser = new NwidartSchemaParser($this->option('fields'));
            return Stub::create('/migration/drop.stub', [
                'class'         => $this->getClass(),
                'table'         => $parser->getTableName(),
                'fields'        => $schemaParser->render(),
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
        $path = Modularity::getModulePath($this->getModuleName());

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

        if($this->option('addPosition')){
            $fields .= "position:integer:unsigned:nullable,";
        }

        if(!$this->option('addTranslation')){
            if(count($this->defaultFieldSchemas))
                $fields .= implode(",", $this->defaultFieldSchemas).",";
            $fields .= $this->option('fields');
        }

        return new SchemaParser(rtrim($fields, ","));
    }

    public function getExtraUpSchemaMethods()
    {
        $schemas = "";
        $singular_table = Str::singular( (new NameParser($this->argument('name')))->getTableName() );

        if($this->option('addTranslation')){
            $fields = implode(",", $this->defaultFieldSchemas).","
                .$this->option('fields');

            $schemas .= "\t\t\tSchema::create('{$singular_table}_translations', function(Blueprint \$table) {\n"
                ."\t\t\tcreateDefaultTranslationsTableFields(\$table, '{$singular_table}');\n"
                // . (new SchemaParser(implode(",", $this->defaultFieldSchemas)))->render()
                . (new SchemaParser(rtrim($fields)))->render()
                ."\t\t});\n\n";
        }

        if($this->option('addSlug')){
            // Schema::create('blog_slugs', function (Blueprint $table) {
            //     createDefaultSlugsTableFields($table, 'blog');
            // });
            $schemas .= "\t\t\tSchema::create('{$singular_table}_slugs', function(Blueprint \$table) {\n"
                ."\t\t\tcreateDefaultSlugsTableFields(\$table, '{$singular_table}');\n"
                ."\t\t});\n\n";
        }

        return $schemas;
    }

    public function getExtraDownSchemaMethods()
    {
        $schemas = "";

        $table = (new NameParser($this->argument('name')))->getTableName();

        $singular_table = Str::singular( $table );

        if($this->option('addTranslation')){
            // $results = "\t\t\t" . '$table';
            $schemas .= "\t\t\tSchema::dropIfExists('{$singular_table}_translations');\n";
        }

        if($this->option('addSlug')){
            $schemas .= "\t\t\tSchema::dropIfExists('{$singular_table}_slugs');\n";
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
