<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class BuildCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:build
        {--noInstall : No install npm packages}
        {--hot : Hot Reload}
        {--w|watch : Watcher for dev}
        {--c|copyOnly : Only copy assets}
        {--cc|copyComponents : Only copy custom components}
        {--ct|copyTheme : Only copy custom theme}
        {--cts|copyThemeScript : Only copy custom theme script}
        {--theme= : Custom theme name if was worked on}';

    protected $aliases = [
        'unusual:build',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the Modularity assets with custom Vue components';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        if ($this->option('copyOnly')) {
            return $this->copyCustoms();
        }

        if ($this->option('copyTheme')) {
            $theme = $this->option('theme');

            return $theme ? $this->copyTheme($theme) : 1;
        }

        if ($this->option('copyThemeScript')) {
            $theme = $this->option('theme');

            return $theme ? $this->copyThemeScript($theme) : 1;
        }

        if ($this->option('copyComponents')) {
            return $this->copyVueComponents();
        }

        return $this->fullBuild();
    }

    /*
     * @return void
     */
    private function fullBuild()
    {
        $progressBar = $this->output->createProgressBar(4);
        $progressBar->setFormat('%current%/%max% [%bar%] %message%');
        // dd( $this->option('noInstall') );
        $npmInstall = ! $this->option('noInstall');

        $progressBar->setMessage(($npmInstall ? 'Installing' : 'Reusing') . " npm dependencies...\n\n");

        $progressBar->start();

        if ($npmInstall) {
            $this->runVueProcess(['npm', 'ci']);
        } else {
            sleep(1);
        }

        if (! file_exists(resource_path(unusualConfig('custom_components_resource_path', 'vendor/modularity/js/components')))) {
            $this->call('vendor:publish', [
                '--provider' => 'Unusualify\Modularity\LaravelServiceProvider',
                '--tag' => 'custom-components',
                '--force' => true,
            ]);
        }

        if (! file_exists(resource_path('vendor/modularity/js/entries'))) {
            $this->filesystem->makeDirectory(resource_path('vendor/modularity/js/entries'));
        }

        if (! file_exists(resource_path('vendor/modularity/themes'))) {
            $this->filesystem->makeDirectory(resource_path('vendor/modularity/themes'));
            $this->filesystem->put(resource_path('vendor/modularity/themes/.keep'), '');
        }

        $this->info('');
        $progressBar->setMessage("Copying custom components...\n\n");
        $progressBar->advance();

        $this->copyCustoms();
        sleep(1);

        $this->info('');
        $progressBar->setMessage("Building assets started...\n\n");
        $progressBar->advance();

        // $resource_path = resource_path('vendor/modularity/js/components/*.vue');

        if ($this->option('hot')) {
            // $this->startWatcher( $resource_path, 'php artisan modularity:build --copyOnly');
            $this->startWatchers();

            // $this->runVueProcess(['npm', 'run', 'serve', '--', "--mode={$mode}", "--port={$this->getDevPort()}"], true);
            // $this->runVueProcess(['npm', 'run', 'serve', '--','--source-map', '--inspect-loader ',"--port={$this->getDevPort()}"], true);
            $this->runVueProcess(['npm', 'run', 'dev'], true);
        } elseif ($this->option('watch')) {
            // $this->startWatcher( $resource_path, 'php artisan modularity:build --copyOnly');
            $this->startWatchers();

            $this->runVueProcess(['npm', 'run', 'watch'], true);
        } else {
            $this->runVueProcess(['npm', 'run', 'build']);

            $this->info('');
            $progressBar->setMessage("Publishing assets...\n\n");
            $progressBar->advance();
            $this->call('modularity:refresh');

            $this->info('');
            $progressBar->setMessage("Done. \n\n");
            $progressBar->finish();
            $progressBar->setMessage("\n\n");
        }

        return 0;
    }

    /**
     * @return string
     */
    private function getDevPort()
    {
        preg_match('/^.*:(\d+)/', $this->baseConfig('development_url'), $matches);

        return $matches[1] ?? '8080';
    }

    /**
     * @return void
     */
    private function startWatcher($pattern, $command)
    {
        if (empty($this->filesystem->glob($pattern))) {
            return;
        }

        $chokidarPath = base_path($this->baseConfig('vendor_path') . '/vue') . '/node_modules/.bin/chokidar';
        $chokidarCommand = [$chokidarPath, $pattern, '-c', $command];

        if ($this->filesystem->exists($chokidarPath)) {
            $process = new Process($chokidarCommand, base_path());
            $process->setTty(Process::isTtySupported());
            $process->setTimeout(null);

            try {
                $process->start();
            } catch (\Exception $e) {
                $this->warn("Could not start the chokidar watcher ({$e->getMessage()})\n");
            }
        } else {
            $this->warn("The `chokidar-cli` package was not found. It is required to watch custom blocks & components in development. You can install it by running:\n");
            $this->warn("    php artisan modularity:dev\n");
            $this->warn("without the `--noInstall` option.\n");
            sleep(2);
        }
    }

    private function startWatchers()
    {
        $resource_path = resource_path('vendor/modularity/js/components/*.vue');
        $this->startWatcher($resource_path, 'php artisan modularity:build --copyComponents');

        $builtinThemes = builtInModularityThemes();
        $customThemes = customModularityThemes();
        $theme = env('VUE_APP_THEME', 'unusual');

        if (! array_key_exists($theme, $builtinThemes->toArray()) && array_key_exists($theme, $customThemes->toArray())) {
            $path = resource_path('vendor/modularity/themes/' . $theme . '/sass/*');
            $this->startWatcher($path, "php artisan modularity:build --copyTheme --theme='{$theme}'");

            $path = resource_path('vendor/modularity/themes/' . "$theme/$theme.js");
            $this->startWatcher($path, "php artisan modularity:build --copyThemeScript --theme='{$theme}'");
        }

    }

    /**
     * @return void
     */
    private function runVueProcess(array $command, $disableTimeout = false)
    {
        $process = new Process($command, base_path($this->baseConfig('vendor_path')) . '/vue');
        $process->setTty(Process::isTtySupported());

        if ($disableTimeout) {
            $process->setTimeout(null);
        } else {
            $process->setTimeout($this->baseConfig('build_timeout', 300));
        }

        $process->mustRun();
    }

    /*
     * @return void
     */
    private function copyCustoms()
    {
        $this->copyVueComponents();

        $builtinThemes = builtInModularityThemes();
        $customThemes = customModularityThemes();
        $theme = env('VUE_APP_THEME', 'unusual');

        if (array_key_exists($theme, $customThemes->toArray())) {
            $this->copyTheme($theme);
            $this->copyThemeScript($theme);
        }

        return 1;
    }

    /**
     * @return int
     */
    private function copyVueComponents()
    {
        $this->info('Copying custom components...');

        $localCustomComponentsPath = resource_path(unusualConfig('custom_components_resource_path', 'vendor/modularity/js/components'));
        $vueCustomComponentsPath = base_path(unusualConfig('vendor_path')) . '/vue/src/js/components/customs';

        $this->copyDirectory($localCustomComponentsPath, $vueCustomComponentsPath, clean: true);

        $this->info('Done.');

        return 1;
        // if (!$this->filesystem->exists($unusualCustomComponentsPath)) {
        //     $this->filesystem->makeDirectory($unusualCustomComponentsPath, 0755, true);
        // }

        // $this->filesystem->cleanDirectory($unusualCustomComponentsPath);
        // $this->filesystem->put($unusualCustomComponentsPath . '/.keep', '');

        // if ($this->filesystem->exists($localCustomComponentsPath)) {
        //     $this->filesystem->copyDirectory($localCustomComponentsPath, $unusualCustomComponentsPath);
        // }
    }

    /**
     * @return int
     */
    private function copyTheme($theme)
    {
        $this->info('Copying custom theme files...');

        $sources = resource_path('vendor/modularity/themes/' . $theme . '/sass');
        $targetPath = base_path(unusualConfig('vendor_path')) . '/vue/src/sass/themes/customs/' . $theme;

        $this->copyDirectory($sources, $targetPath);

        $this->info('Done.');

        return 1;
    }

    /**
     * @return int
     */
    private function copyThemeScript($theme)
    {
        $this->info('Copying custom theme script...');

        $source = resource_path('vendor/modularity/themes/' . "{$theme}/{$theme}.js");
        $targetPath = base_path(unusualConfig('vendor_path')) . '/vue/src/js/config/themes/customs/' . $theme . '.js';

        $this->copyFile($source, $targetPath);

        $this->info('Done.');

        return 1;
    }

    private function copyDirectory($files, $target, $clean = false)
    {
        if (! $this->filesystem->exists($target)) {
            $this->filesystem->makeDirectory($target, 0755, true);
        }

        if ($clean) {
            $this->filesystem->cleanDirectory($target);
            $this->filesystem->put($target . '/.keep', '');
        }

        if ($this->filesystem->exists($files)) {
            $this->filesystem->copyDirectory($files, $target);
        }
    }

    private function copyFile($file, $target, $clean = false)
    {
        // if (!$this->filesystem->exists($target)) {
        //     $this->filesystem->makeDirectory($target, 0755, true);
        // }

        // if($clean){
        //     $this->filesystem->cleanDirectory($target);
        //     $this->filesystem->put($target . '/.keep', '');
        // }

        if ($this->filesystem->exists($file)) {
            $this->filesystem->copy($file, $target);
        }
    }
}
