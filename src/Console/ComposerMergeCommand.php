<?php

namespace Unusualify\Modularity\Console;

use Symfony\Component\Console\Input\InputOption;

class ComposerMergeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:composer:merge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "To add merge-plugin require pattern for composer-merge-plugin package";

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle() :int
    {
        $composerPath = base_path( $this->option('production') ? 'composer.json' : 'composer-dev.json');
        $modulesFolderName = trim(config('modules.paths.modules', 'modules'), '\/');
        $modulesPattern = "$modulesFolderName/*/composer.json";

        $composer = $this->laravel['files']->json($composerPath);

        $composer['extra'] = array_merge_recursive_preserve($composer['extra'], [
            'merge-plugin' => [
                'require' => [
                    $modulesPattern
                ]
            ]
        ]);

        $composer['extra']['merge-plugin']['require'] = array_unique($composer['extra']['merge-plugin']['require']);

        // "Modules/*/composer.json"

        // $composer->extra
        if( $this->laravel['files']->put($composerPath, collect($composer)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ){
            $this->info("Merge-plugin require patterns updated on {$composerPath} file...\n");
        };
        // dd(
        //     $composerPath,
        //     $this->option('production'),
        // );

        return 0;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['production', '--p', InputOption::VALUE_NONE, 'Update Production composer.json file'],
        ];
    }
}
