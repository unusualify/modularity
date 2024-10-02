<?php

namespace Unusualify\Modularity\Console;

use Nwidart\Modules\Support\Stub;

class AddComposerScriptsCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unusual:composer:scripts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add modularity composer scripts to composer-dev.json';

    protected $aliases = [
        'modularity:composer:scripts',
    ];

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $composerPath = base_path('composer-dev.json');

        $scriptsStub = new Stub('/composer-scripts.json', []);

        $composer = $this->laravel['files']->json($composerPath);
        $scripts = $this->laravel['files']->json($scriptsStub->getPath());

        $composer['scripts'] = array_merge($composer['scripts'], $scripts);

        if ($this->laravel['files']->put($composerPath, collect($composer)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
            $this->info("Modularity composer scripts were updated on {$composerPath} file...\n");
        }

        return 0;
    }
}
