<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ThemeMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:make:theme';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generalize a theme.';

    protected $defaultReject = true;

    protected $isAskable = false;

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

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        $success = true;

        $jsSource = resource_path("vendor/modularity/themes/{$name}/{$name}.js");
        if (! $this->filesystem->isFile($jsSource)) {
            $this->comment("{$jsSource} file not exists");

            return E_ERROR;
        }

        $sassSource = resource_path("vendor/modularity/themes/{$name}/sass");
        if (! $this->filesystem->isDirectory($sassSource)) {
            $this->comment("{$sassSource} directory not exists");

            return E_ERROR;
        }

        $jsTarget = base_path(unusualConfig('vendor_path') . "/vue/src/js/config/themes/{$name}.js");
        $sassTarget = base_path(unusualConfig('vendor_path') . "/vue/src/sass/themes/{$name}");

        $this->filesystem->copy(
            $jsSource, $jsTarget
        );

        $this->filesystem->copyDirectory(
            $sassSource, $sassTarget
        );

        // delete custom modularity paths
        $this->filesystem->delete(base_path(unusualConfig('vendor_path') . "/vue/src/js/config/themes/customs/{$name}.js"));
        $this->filesystem->deleteDirectory(base_path(unusualConfig('vendor_path') . "/vue/src/sass/themes/customs/{$name}"));

        // delete custom resource path
        $this->filesystem->deleteDirectory(resource_path("vendor/modularity/themes/{$name}"));

        $this->writeThemeExport($name);

        $this->info("The {$name} theme has been generalized!");

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
            ['name', InputArgument::REQUIRED, 'The name of custom theme to be generalized.'],
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
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
        ]);
    }

    protected function writeThemeExport($themeName)
    {
        $filePath = base_path(unusualConfig('vendor_path') . '/vue/src/js/config/themes/index.js');

        $content = get_file_string($filePath);

        $content .= "export { default as {$themeName} } from './{$themeName}'\n";

        app('files')->put($filePath, $content);
    }
}
