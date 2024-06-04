<?php

use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Unusualify\Modularity\Facades\Modularity;

return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
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
    protected ?string $tag = null;

    /**
     * Process the operation.
     */
    public function process(): void
    {
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

        $this->output->writeln('');
        $this->output->writeln('');

        $this->info("\t'{$old_folder_name}' folder name changed as '{$new_folder_name}'.");

        $this->output->writeln('');
    }
};
