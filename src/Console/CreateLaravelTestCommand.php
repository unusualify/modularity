<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
// use Illuminate\Console\Command as Console;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\text;

class CreateLaravelTestCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:create:test:laravel';

    /**
     * The signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unusual:create:test:laravel {module} {test} {--unit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test file for laravel features or components';

    // protected $argumentName = 'request';

    /**
     * The laravel console instance.
     *
     * @var Console
     */
    protected $console;

    public function handle(): int
    {

        $success = true;

        $test_name = $this->argument('name') ? $this->getStudlyName($this->argument('name')) : '';

        $test_type = $this->argument('type') ? $this->getSnakeCase($this->argument('type')) : '';

        if (! $test_name) {
            $test_name = $this->getStudlyName(text('What is the test name?'));
        }

        $testGenerator = $this->laravel->make('Unusualify\Modularity\Generators\LaravelTestGenerator', ['name' => $test_name]);

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
            ['module', InputArgument::REQUIRED, 'The name of the module.'],
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
            ['unit', '--unit', InputOption::VALUE_NONE, 'The specified test will be generated as unit test.'],
            // ['schema', null, InputOption::VALUE_OPTIONAL, 'The specified migration schema table.', null],
            // ['rules', null, InputOption::VALUE_OPTIONAL, 'The specified validation rules for FormRequest.', null],
            // ['relationships', null, InputOption::VALUE_OPTIONAL, 'The many to many relationships.', null],
            // ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            // ['plain', null, InputOption::VALUE_NONE, 'Don\'t create route.'],
            // ['no-migrate', null, InputOption::VALUE_NONE, 'don\'t migrate.'],
            // ['no-defaults', null, InputOption::VALUE_NONE, 'unuse default input and headers.'],
            // ['notAsk', null, InputOption::VALUE_NONE, 'don\'t ask for trait questions.'],
            // ['all', null, InputOption::VALUE_NONE, 'add all traits.'],
        ], unusualTraitOptions());
    }

    /**
     * Get head string of path for namespace
     */
    public function getDefaultNamespace(): string
    {
        // dd($this->baseConfig('paths.generator.controller-api.namespace'), $this->baseConfig('paths.generator.controller-api.path', 'Https\Controllers\API'));
        return $this->baseConfig('paths.generator.route-request.namespace') ?:
            $this->baseConfig('paths.generator.route-request.path', 'Http\Requests');
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('module'));
    }

    private function checkOption($option)
    {

        if (! $this->hasOption($option)) {
            return false;
        }

        if ($this->option($option) || $this->option('all')) {
            return true;
        }

        if (! $this->isAskable()) {
            return false;
        }

        $questions = Collection::make($this->baseConfig('traits'))->mapWithKeys(function ($object, $key) {
            return [$key => $object['question']];
        })->toArray();

        $defaultAnswers = [
            'nestingTrait' => 0,
        ];

        $currentDefaultAnswer = $this->defaultReject ? 0 : ($defaultAnswers[$option] ?? 1);

        // dd(
        //     $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer)
        // );
        return $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer) === 'yes';
    }
}
