<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularity\Facades\Modularity;

class CreateOperationCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:create:operation
        {name : The name of the operation}
        {--self : The path of the modularity}
        {--path= : The path of the operation}
        {--t|tag= : The tag of the operation}
        {--async : The operation will be processed asynchronously}
        {--queue=default : The queue that the job will be dispatched to}
    ';

    protected $aliases = [
        'modularity:operations:make',
        'mod:c:operation',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an operation with the modularity tag in order to use in timokoerber/laravel-one-time-operations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $snakeName = Str::snake($name);
        $headlineName = Str::headline($name);
        $tag = $this->option('tag');
        $path = null;
        $fileNameSegments = [$this->getDatePrefix()];

        if ($this->option('self')) {
            $path = Modularity::getVendorPath('operations');
            $tag = $tag ?? 'modularity';
            $fileNameSegments[] = 'modularity';

        } else {
            $path = $this->option('path') ?? base_path(config('one-time-operations.directory', 'operations'));

            if (! Str::startsWith($path, '/')) {
                $path = base_path($path);
            }
        }

        if (! File::exists($path)) {
            $this->error("Path does not exist: {$path}");

            return 1;
        }

        $fileNameSegments[] = $snakeName;
        $fileNameSegments[] = 'operation';

        $fileName = implode('_', $fileNameSegments) . '.php';

        $replacements = [
            'NAME' => $headlineName,
            'TAG' => $tag,
            'ASYNC' => $this->option('async') ? 'true' : 'false',
            'QUEUE' => $this->option('queue'),
        ];

        $content = (new Stub('/operation.stub', $replacements))->render();

        $path = concatenate_path($path, $fileName);

        File::put($path, $content);

        $this->info("Operation created successfully: {$path}");

        return 0;
    }

    protected function getDatePrefix(): string
    {
        return Carbon::now()->format('Y_m_d_His');
    }
}
