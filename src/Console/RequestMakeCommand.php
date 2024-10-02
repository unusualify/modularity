<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Support\Decomposers\ValidatorParser;

class RequestMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:make:request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create form request for specified module.';

    protected $argumentName = 'request';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        $this->info('Request created successfully!');

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
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['request', InputArgument::REQUIRED, 'The name of the request class.'],
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
            ['rules', null, InputOption::VALUE_OPTIONAL, 'The validation rules.', null],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $request = $this->argument('request');

        $module = Modularity::findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'BASE_REQUEST_CLASS' => $this->baseConfig('base_request'),
            'BASE_REQUEST_NAME' => get_class_short_name($this->baseConfig('base_request')),
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClass() . 'Request',
            'RULES' => $this->getRules(),
        ]))->render();

    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = Modularity::getModulePath($this->getModuleName());

        $prePath = new GeneratorPath($this->baseConfig('paths.generator.route-request'));

        // dd($prePath, $path . $prePath->getPath() . '/' . $this->getFileName() . 'Request.php' );

        return $path . $prePath->getPath() . '/' . $this->getFileName() . 'Request.php';
    }

    /**
     * @return string
     */
    private function getRules()
    {
        $rule_schema = $this->option('rules');

        return (new ValidatorParser($rule_schema))->toReplacement();

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
        return Str::studly($this->argument('request'));
    }

    protected function getStubName(): string
    {
        return '/route-request.stub';
    }
}
