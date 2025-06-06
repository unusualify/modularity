<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;

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
        $signature = str_replace(['\t', '\n'], ["\t", "\n"], $signature);

        $created = "\t/**\n"
            . "\t * The name and signature of the console command.\n"
            . "\t *\n"
            . "\t * @var string\n"
            . "\t */\n"
            . "\tprotected \$signature = 'modularity:{$signature}';";

        $replacements = [
            'STUDLY_NAME' => $studlyName,
            'SIGNATURE' => $created,
            'DESCRIPTION' => $description,
        ];

        $content = (new Stub('/scaffold/command.stub', $replacements))->render();

        $path = get_modularity_vendor_path('src/Console/' . $studlyName . 'Command.php');

        $this->filesystem->put($path, $content);

        $this->info("Command created successfully: {$path}");

        return 0;
    }
}
