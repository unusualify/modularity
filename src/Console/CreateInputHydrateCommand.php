<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;

class CreateInputHydrateCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:create:input:hydrate';

    protected $aliases = [
        'mod:c:input:hydrate',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Input Hydrate Class.';

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
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        $studlyName = studlyName($name);

        $success = true;

        $className = "{$studlyName}Hydrate";

        $path = get_modularity_vendor_path("src/Hydrates/Inputs/{$className}.php");

        if (! file_exists($path)) {
            $content = (string) new Stub('/input-hydrate.stub', ['name' => $className]);

            $this->filesystem->put($path, $content);

            $this->info("{$className} class created.");
        } else {
            $success = false;

            $this->warn("{$className} class already exists!");
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
            ['name', InputArgument::REQUIRED, 'The name of theme to be created.'],
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
        ]);
    }
}
