<?php

namespace Unusualify\Modularous\Console\Setup;

use Illuminate\Support\Facades\Process;
use Unusualify\Modularous\Console\BaseCommand;

use function Laravel\Prompts\text;

class SetupModularousDevelopmentCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:setup:development
        {branch? : The name of branch to work.}
        {repo?=unusualify/modularous : The name of repository to work.}
        {timeout?=240 : The timeout for the git clone command.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup modularous development on local';

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {

        $composerPath = base_path('composer.json');
        $composer = $this->laravel['files']->json($composerPath);

        if (isset($composer['repositories'])) {
            $this->alert("Composer has 'repositories' key, we cannot configure development environment!");

            return 0;
        }

        $branch = $this->argument('branch') ?? '';

        if (! $branch) {
            $branch = text(
                label: 'What is your target branch?',
                placeholder: 'Default branch is "dev"',
                default: 'dev',
                hint: 'This will make a checkout to the specified branch.'
            );
        }

        $composer['name'] = 'unusualify/modularous-dev';
        $composer['description'] = 'The Laravel Framework powered with Modularous.';
        $composer['minimum-stability'] = 'dev';
        $composer['repositories'] = [
            [
                'type' => 'path',
                'url' => './packages/*',
                'options' => [
                    'symlink' => true,
                ],
            ],
        ];
        $repo = $this->argument('repo') ?? 'unusualify/modularous';
        $folderName = explode('/', $repo)[1];
        $timeout = $this->argument('timeout') ?? 240;

        $composer['require'] = array_merge_recursive_preserve($composer['require'], [
            $repo => '*',
        ]);

        $packagesFolder = base_path('packages');

        if (! $this->laravel['files']->isDirectory($packagesFolder)) {
            $this->laravel['files']->makeDirectory($packagesFolder);
        }

        if ($this->laravel['files']->isDirectory(base_path('packages/' . $folderName))) {
            $this->alert("Repository cannot be cloned! '" . base_path('packages/modularous') . "' folder already exists.");

            return 0;
        }

        $result = Process::timeout(240)
            ->path(base_path('packages'))
            ->run('git clone --progress https://github.com/' . $repo . '.git ' . $folderName, function (string $type, string $output) {
                if ($type === 'err') {
                    // git clone ilerleme bilgisini stderr'e yazar!
                    $this->getOutput()->write("<comment>{$output}</comment>");
                } else {
                    $this->getOutput()->write($output);
                }
            });

        if ($result->failed()) {
            $this->alert("Repository couldn't be cloned! Try Later again.");

            return 0;
        }

        if (! $this->laravel['files']->isDirectory(base_path('packages/modularous'))) {
            $this->alert("Repository couldn't be cloned! Try Later again.");

            return 0;
        }

        Process::path(base_path('packages/' . $folderName))->run('git fetch');

        $result = Process::path(base_path('packages/' . $folderName))->run("git checkout {$branch}");

        $this->info($result->output());

        if ($this->laravel['files']->put(base_path('composer-dev.json'), collect($composer)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
            $this->info("composer-dev.json file created on root path.\n");
        }

        $this->call('modularous:composer:scripts');

        $this->alert('For getting into development process, run commands as following:');
        $this->warn("rm -rf vendor && rm -rf composer-dev.lock \n");
        $this->warn("COMPOSER=composer-dev.json composer install \n");

        return 0;
    }
}
