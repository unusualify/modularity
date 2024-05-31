<?php

namespace Unusualify\Modularity\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Nwidart\Modules\Support\Stub;

use function Laravel\Prompts\{text, select, confirm, warning};

class CreateVueTestCommand extends BaseCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:create:test:vue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create a test file for vue features or components";

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle() :int
    {
        $success = true;

        $test_name = $this->argument('name') ? $this->getStudlyName($this->argument('name')) : '';

        $test_type = $this->argument('type') ? $this->getSnakeCase($this->argument('type')) : '';

        if( !$test_name )
            $test_name = $this->getStudlyName(text('What is the test name?'));

        $vueTestGenerator = $this->laravel->make('Unusualify\Modularity\Generators\VueTestGenerator', ['name' => $test_name]);

        if( !$test_type )
            $test_type = select(
                label: 'What is type of the test?',
                options: array_keys($vueTestGenerator->getTypes())
            );


        $vueTestGenerator = $vueTestGenerator->setType($test_type)
            ->setFilesystem($this->laravel['files'])
            ->setConfig($this->laravel['config'])
            ->setConsole($this);

        $subImportDir = $this->option('importDir');

        if($subImportDir)
            $vueTestGenerator = $vueTestGenerator->setSubImportDir($subImportDir);

        $code = $vueTestGenerator->generate();

        if ($code === E_ERROR) {
            $success = false;
        }

        return $success ? 0 : E_ERROR;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of test will be used.'],
            ['type', InputArgument::OPTIONAL, 'The type of test.'],
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
            ['importDir', null, InputOption::VALUE_OPTIONAL, 'Vue subfolder for importing.', null],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
        ];
    }
}
