<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;

class CreateVueInputCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:create:vue:input';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Vue Input Component.';

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

        $componentName = studlyName($name);
        $componentKebabName = 'v-input-' . kebabCase($name);

        $path = base_path(unusualConfig('vendor_path') . '/vue/src/js/components/inputs/') . "{$componentName}.vue";

        if (! file_exists($path)) {
            $content = (string) new Stub('/input-component.vue', ['name' => $componentKebabName]);

            $this->filesystem->put($path, $content);

            $this->info("{$componentName} component created.");
        } else {
            $success = false;

            $this->warn("{$componentName} component already exists!");
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
