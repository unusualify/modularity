<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\{text, info, alert, warn};

class SetupModularityDevelopmentCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unusual:setup:development
        {branch? : The name of branch to work.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Setup modularity development on local";

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle() :int
    {

        $composerPath = base_path('composer.json');
        $composer = $this->laravel['files']->json($composerPath);

        if(isset($composer['repositories'])){
            $this->alert("Composer has 'repositories' key, we cannot configure development environment!");

            return 0;
        }

        $branch = $this->argument('branch') ?? '';

        if(!$branch){
            $branch = text(
                label: 'What is your target branch?',
                placeholder: 'Default branch is "dev"',
                default: 'dev',
                hint: 'This will make a checkout to the specified branch.'
            );
        }

        $composer["name"] = "unusualify/modularity-dev";
        $composer["description"] = "The Laravel Framework powered with Modularity.";
        $composer["minimum-stability"] = "dev";
        $composer["repositories"] = [
            [
                "type"=> "path",
                "url"=> "./packages/*",
                "options"=> [
                    "symlink"=> true
                ]
            ]
        ];

        $composer['require'] = array_merge_recursive_preserve($composer['require'], [
            "unusualify/modularity" => "*",
        ]);

        $packagesFolder = base_path('packages');

        if(!$this->laravel['files']->isDirectory($packagesFolder)){
            $this->laravel['files']->makeDirectory($packagesFolder);
        }

        if($this->laravel['files']->isDirectory(base_path('packages/modularity'))){
            $this->alert("Repository cannot be cloned! '". base_path('packages/modularity') . "' folder already exists.");

            return 0;
        }

        Process::timeout(120)->path(base_path('packages'))->run('git clone https://github.com/unusualify/modularity.git modularity');

        if(!$this->laravel['files']->isDirectory(base_path('packages/modularity'))){
            $this->alert("Repository couldn't be cloned! Try Later again.");

            return 0;
        }

        Process::path(base_path('packages/modularity'))->run('git fetch');

        $result = Process::path(base_path('packages/modularity'))->run("git checkout {$branch}");

        $this->info($result->output());

        if( $this->laravel['files']->put(base_path('composer-dev.json'), collect($composer)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ){
            $this->info("composer-dev.json file created on root path.\n");
        };

        $this->call('unusual:composer:scripts');

        $this->alert('For getting into development process, run commands as following:');
        $this->warn("rm -rf vendor && rm -rf composer-dev.lock \n");
        $this->warn("COMPOSER=composer-dev.json composer install \n");

        return 0;
    }
}
