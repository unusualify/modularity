<?php

namespace Unusualify\Modularity\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateVueTestCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:create:vue:test
        {name? : The name of test will be used.}
        {type? : The type of test.}
        {--importDir : The subfolder for importing.}
        {--F|force : Force the operation to run when the route files already exist.}';

    protected $aliases = [
        'mod:c:vue:test'
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test file for vue features or components';

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $success = true;

        $test_name = $this->argument('name') ? $this->getStudlyName($this->argument('name')) : '';

        $test_type = $this->argument('type') ? $this->getSnakeCase($this->argument('type')) : '';

        if (! $test_name) {
            $test_name = $this->getStudlyName(text('What is the test name?'));
        }

        $vueTestGenerator = $this->laravel->make('Unusualify\Modularity\Generators\VueTestGenerator', ['name' => $test_name]);

        if (! $test_type) {
            $test_type = select(
                label: 'What is type of the test?',
                options: array_keys($vueTestGenerator->getTypes())
            );
        }

        $vueTestGenerator = $vueTestGenerator->setType($test_type)
            ->setFilesystem($this->laravel['files'])
            ->setConfig($this->laravel['config'])
            ->setConsole($this);

        $subImportDir = $this->option('importDir');

        if ($subImportDir) {
            $vueTestGenerator = $vueTestGenerator->setSubImportDir($subImportDir);
        }

        $code = $vueTestGenerator->generate();

        if ($code === E_ERROR) {
            $success = false;
        }

        return $success ? 0 : E_ERROR;
    }
}
