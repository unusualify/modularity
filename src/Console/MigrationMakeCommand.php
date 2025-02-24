<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Migrations\NameParser;
use Nwidart\Modules\Support\Migrations\SchemaParser as NwidartSchemaParser;
use Nwidart\Modules\Support\Stub;
// use Nwidart\Modules\Support\Migrations\SchemaParser;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Support\Decomposers\SchemaParser;

class MigrationMakeCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    // protected $name = 'modularity:make:migration';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:make:migration
        {name : The migration name will be created}
        {module? : The name of module that the migration will be created in}
        {--self : Create a modularity migration}
        {--fields= : The specified fields table}
        {--route= : The route name for pivot table}
        {--plain : Create plain migration}
        {--f|force : Force the operation to run when the route files already exist}
        {--relational= : Create relational table for many-to-many and polymorphic relationships}
        {--notAsk : Don\'t ask for trait questions}
        {--no-defaults : Unuse default input and headers}
        {--all : Add all traits}
        {--table-name= : Set table name}
        {--test : Test the Route Generator}';

    protected $aliases = [
        'mod:m:migration',
    ];

    public $useTraitOptions = true;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration for the specified module.';

    protected $defaultFieldSchemas = [];

    protected $nonTranslatableFillable = [];

    protected $nonMigrationFields = [];

    /**
     * Run the command.
     */
    public function handle(): int
    {
        if (! $this->option('no-defaults')) {
            $this->nonMigrationFields = $this->baseConfig('schemas.non_migration_fields', []);
            $this->defaultFieldSchemas = array_filter($this->baseConfig('schemas.default_fields') ?? [], function($field) {
                $field = explode(':', $field)[0];
                return !in_array($field, $this->nonMigrationFields);
            });
        }


        if($this->option('addSingular')){
            $this->info('Pass making migration due to addSingular option!');

            return 0;
        }

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        if ($this->option('relational')) {
            $this->info('(Pivot) Migration created successfully!');
        } else {
            $this->info('Migration created successfully!');
        }

        return 0;
    }

    /**
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function getTemplateContents()
    {
        $parser = new NameParser($this->argument('name'));
        // Table name option added. If table name option is not set get from parser.
        $tableName = $this->option('table-name') ? $this->option('table-name') : $parser->getTableName();

        if (($relational = $this->option('relational'))) {
            if ($relational == 'BelongsToMany') {
                $table1 = $this->getSnakeCase($this->option('route'));
                $table2 = preg_replace("/^({$table1}_)(\w+)$/", '${2}', $tableName);

                return Stub::create('/migration/pivot.stub', [
                    'class' => $this->getClass(),
                    'table' => $tableName,
                    'fields' => ltrim((new SchemaParser(schema: $this->option('fields'), useDefaults: false))->render()),
                    // 'fields'        => ltrim($this->getSchemaParser()->render()),

                    'table1' => $table1,
                    'table2' => $table2,
                ]);
            } elseif ($relational == 'MorphedByMany') {

                if($this->option('route')){
                    $modelName = Str::studly($this->option('route'));
                }else{
                    preg_match('/^create_(.*)_table$/', $this->option('name'), $matches);
                    $modelName = Str::studly(getMorphModelName($matches[1]));
                }
                $morphedTableName = tableName($modelName);

                return Stub::create('/migration/morphPivot.stub', [
                    'class' => $this->getClass(),
                    'table' => $tableName,
                    'modelName' => $modelName,
                    'morphedTableName' => $morphedTableName,
                    'fields' => ltrim((new SchemaParser(useDefaults: false))->render()),
                ]);
            }
        } elseif ($parser->isCreate()) {
            return Stub::create('/migration/create.stub', [
                'class' => $this->getClass(),
                'table' => $tableName,
                'fields' => ltrim($this->getSchemaParser()->render()),
                'up_schemas' => ltrim($this->getExtraUpSchemaMethods()),
                'down_schemas' => ltrim($this->getExtraDownSchemaMethods()),
            ]);
        } elseif ($parser->isAdd()) {
            $schemaParser = new NwidartSchemaParser($this->option('fields'));

            return Stub::create('/migration/add.stub', [
                'class' => $this->getClass(),
                'table' => $tableName,
                'fields_up' => ltrim(rtrim($schemaParser->up())),
                'fields_down' => ltrim(rtrim($schemaParser->down())),
            ]);
        } elseif ($parser->isDelete()) {
            $schemaParser = new NwidartSchemaParser($this->option('fields'));

            return Stub::create('/migration/delete.stub', [
                'class' => $this->getClass(),
                'table' => $tableName,
                'fields_down' => $schemaParser->up(),
                'fields_up' => $schemaParser->down(),
            ]);
        } elseif ($parser->isDrop()) {
            $schemaParser = new NwidartSchemaParser($this->option('fields'));

            return Stub::create('/migration/drop.stub', [
                'class' => $this->getClass(),
                'table' => $tableName,
                'fields' => $schemaParser->render(),
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
        $moduleName = $this->getModuleName();

        $path = base_path();
        $migrationFolder = 'database/migrations';

        $fileName = $this->getFileName();

        if ($moduleName != '') {
            $path = Modularity::getModulePath($moduleName);
            $migrationGeneratorPath = GenerateConfigReader::read('migration');
            $migrationFolder = $migrationGeneratorPath->getPath();
        }else{
            if($this->option('self')){
                $path = Modularity::getVendorPath('');
                $migrationFolder = 'database/migrations/default';
            }
        }

        $migrationDir = concatenate_path($migrationFolder, $fileName . '.php');

        return concatenate_path($path, $migrationDir);
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        $fields = '';

        if ($this->option('addPosition')) {
            $fields .= 'position:integer:unsigned:nullable,';
        }

        // dd($this->defaultFieldSchemas, $this->nonTranslatableFillable);

        if (! $this->option('addTranslation')) {
            if (count($this->defaultFieldSchemas)) {
                $fields .= implode(',', $this->defaultFieldSchemas) . ',';
            }
            $fields .= $this->option('fields');
        }

        // dd(new SchemaParser(rtrim($fields, ',')), $fields, $this->nonTranslatableFillable, $this->defaultFieldSchemas);
        return new SchemaParser(rtrim($fields, ','));
    }

    public function getExtraUpSchemaMethods()
    {
        $schemas = '';
        $singular_table = Str::singular((new NameParser($this->argument('name')))->getTableName());

        if ($this->option('addTranslation')) {
            $fields = implode(',', array_merge($this->defaultFieldSchemas, $this->option('fields') ? explode(',', $this->option('fields')) : []));

            $schemas .= "\t\t\tSchema::create('{$singular_table}_translations', function(Blueprint \$table) {\n"
                . "\t\t\tcreateDefaultTranslationsTableFields(\$table, '{$singular_table}');\n"
                // . (new SchemaParser(implode(",", $this->defaultFieldSchemas)))->render()
                . (new SchemaParser(rtrim($fields)))->render()
                . "\t\t});\n\n";
        }

        if ($this->option('addSlug')) {
            // Schema::create('blog_slugs', function (Blueprint $table) {
            //     createDefaultSlugsTableFields($table, 'blog');
            // });
            $schemas .= "\t\t\tSchema::create('{$singular_table}_slugs', function(Blueprint \$table) {\n"
                . "\t\t\tcreateDefaultSlugsTableFields(\$table, '{$singular_table}');\n"
                . "\t\t});\n\n";
        }

        return $schemas;
    }

    public function getExtraDownSchemaMethods()
    {
        $schemas = '';

        $table = (new NameParser($this->argument('name')))->getTableName();

        $singular_table = Str::singular($table);

        if ($this->option('addTranslation')) {
            // $results = "\t\t\t" . '$table';
            $schemas .= "\t\t\tSchema::dropIfExists('{$singular_table}_translations');\n";
        }

        if ($this->option('addSlug')) {
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
