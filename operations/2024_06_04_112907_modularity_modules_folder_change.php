<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;

return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput;
    }

    /**
     * Determine if the operation is being processed asynchronously.
     */
    protected bool $async = true;

    /**
     * The queue that the job will be dispatched to.
     */
    protected string $queue = 'default';

    /**
     * A tag name, that this operation can be filtered by.
     */
    protected ?string $tag = 'modularity:local';

    /**
     * Process the operation.
     */
    public function process(): void
    {
        if (app()->environment('local')) {
            $old_folder_name = 'Modules';
            $new_folder_name = 'modules';
            $old_modules_path = base_path($old_folder_name);
            $temp_modules_path = base_path('_modules');
            $new_modules_path = base_path($new_folder_name);

            $modulesConfigPath = base_path('config/modules.php');
            $contents = File::get($modulesConfigPath);
            $injection = "'modules' => base_path('{$new_folder_name}'),";

            $pattern = "/'modules'\s\=\>\sbase_path\('" . $old_folder_name . "'\),/";

            $contents = preg_replace($pattern, $injection, $contents);

            File::put($modulesConfigPath, $contents);

            rename($old_modules_path, $temp_modules_path);
            rename($temp_modules_path, $new_modules_path);

            $this->composerUpdate('composer.json', $new_folder_name);
            $this->composerUpdate('composer-dev.json', $new_folder_name);

            $this->output->writeln('');
            $this->output->writeln('');

            $this->info("'{$old_folder_name}' folder name changed as '{$new_folder_name}'.\n");
            $this->alert('Composer files changed. You must run command as following:');
            $this->warn("\tcomposer dump-autoload\n");

            $this->output->writeln('');
        } else {
            $this->info("\tSkipping modules folder change in non-local environment.");
        }
    }

    public function composerUpdate($composerName, $new_folder_name)
    {
        $composerPath = base_path($composerName);

        if (File::isFile($composerPath)) {
            $composer = File::json($composerPath);
            $composer['autoload']['psr-4']['Modules\\'] = "{$new_folder_name}/";

            if (isset($composer['extra']['merge-plugin'])) {
                $composer['extra'] = array_merge_recursive_preserve($composer['extra'], [
                    'merge-plugin' => [
                        'require' => [
                            "{$new_folder_name}/*/composer.json",
                        ],
                    ],
                ]);
                $composer['extra']['merge-plugin']['require'] = array_unique($composer['extra']['merge-plugin']['require']);
            }

            File::put($composerPath, collect($composer)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return true;
    }
};
