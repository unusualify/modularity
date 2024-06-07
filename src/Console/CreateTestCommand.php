<?php

namespace Unusualify\Modularity\Console;

use Exception;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Str;

// use Illuminate\Console\Command as Console;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularity\Module;

class CreateTestCommand extends BaseCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:make:test';

    /**
     * The signature of the console command.
     *
     * @var string
     */

    protected $signature = 'unusual:make:test {module} {test} {--unit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test';

    // protected $argumentName = 'request';

    /**
     * The laravel console instance.
     *
     * @var Console
     */
    protected $console;

    public function handle() : int
    {

        // $console = new Console();

        // Locate the module based on the name and find it's relative path
        $moduleName = $this->argument('module');
        $testName = $this->argument('test');
        $unit = $this->option('unit');
        $module = new Module($this->getModuleName());

        // Create test in the project folder
        // $this->call('make:test', [
        //     'name' => $testName,
        //     '--pest' => true,
        //     ]
        //     + ($this->hasOption('unit') ?  ['--unit' => $unit] : [])
        // );

        if($unit){
            $defaultPath = '/tests/'.'Unit/' . $testName . '.php';
            // $destination = 'Modules/' . $moduleName . '/Tests/' . 'Unit/' . $testName . '.php';
            $destination = $module->getDirectoryPath('Tests/Unit') . $testName . '.php';
        }else{
            $defaultPath = '/tests/' . 'Feature/' . $testName . '.php';
            $destination = 'Modules/' . $moduleName.'/Tests/' . 'Feature/' . $testName . '.php';
        }
           dd($destination);
        // dd(File::directories('.'));
        // dd(Storage::disk('root')->exists($defaultPath), $defaultPath);
        // File::move();
        if(File::exists($defaultPath)){
            dd(File::move($defaultPath, $destination), $destination, $defaultPath);
        }
        



        // dd($arguments, $options);
        // foreach(getUnusualTraits() as $_trait){
        //     $this->responses[$_trait] = $this->checkOption($_trait);
        // }

        // $console_traits = collect($traits)->mapWithKeys(function ($item, $key) {
        //     return ["--{$key}" => $item];
        // })->toArray();

        // dd($console_traits);

        // $this->call('module:make',[
        //     'name' => [$this->argument('module')],
        //     '--plain' => true
        // ]);

        // $this->call('unusual:make:route', [
        //         'module' => $this->argument('module'),
        //         'route' => $this->argument('module'),
        //     ]
        //     + ( $this->hasOption('schema') ?  ['--schema' => $this->option('schema')] : [])
        //     + ( $this->hasOption('rules') ?  ['--rules' => $this->option('rules')] : [])
        //     + ( $this->hasOption('relationships') ?  ['--relationships' => $this->option('rules')] : [])
        //     + ( $this->hasOption('force') ?  ['--force' => true] : [])
        //     + ( $this->hasOption('no-migrate') ?  ['--no-migrate' => true] : [])
        //     + ( $this->hasOption('no-defaults') ?  ['--no-defaults' => true] : [])
        //     + ( $this->option('plain') ?  ['-p' => true] : [])
        //     + $console_traits
        //     + ['--notAsk' => true]
        // );


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
     *
     * Get head string of path for namespace
     *
     * @return string
     */
    public function getDefaultNamespace() : string
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

        if ( !$this->isAskable() )
            return false;

        $questions = Collection::make($this->baseConfig('traits'))->mapWithKeys(function($object, $key){
            return [ $key => $object['question']];
        })->toArray();

        $defaultAnswers = [
            'nestingTrait' => 0,
        ];

        $currentDefaultAnswer = $this->defaultReject ? 0 : ($defaultAnswers[$option] ?? 1);
        // dd(
        //     $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer)
        // );
        return 'yes' === $this->choice($questions[$option], ['no', 'yes'], $currentDefaultAnswer);
    }

}
