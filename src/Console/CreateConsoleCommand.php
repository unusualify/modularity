<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Support\Stub;
use Illuminate\Support\Str;

class CreateConsoleCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:create:command {name} {signature}
        {--d|description= : The description of the command}';

    protected $aliases = [
        'mod:c:cmd',
    ];

        /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new console command';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;

        Stub::setBasePath(dirname(__FILE__) . '/stubs');
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $studlyName = Str::studly($name);
        $headline = Str::headline($name);
        $signature = $this->argument('signature');
        $description = $this->option('description') ?? "{$headline} description";

        $replacements = [
            'STUDLY_NAME' => $studlyName,
            'SIGNATURE' => $signature,
            'DESCRIPTION' => $description,
        ];

        $content = (new Stub("/scaffold/command.stub", $replacements))->render();

        $path = base_path(unusualConfig('vendor_path') . '/src/Console/' . $studlyName . 'Command.php');

        $this->filesystem->put($path, $content);

        return 0;
    }
}
