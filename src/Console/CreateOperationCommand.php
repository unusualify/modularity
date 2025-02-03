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
        {--t|tag=modularity : The tag of the operation}
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

        $replacements = [
            'NAME' => $headlineName,
            'TAG' => $tag,
        ];

        $content = (new Stub('/operation.stub', $replacements))->render();

        $path = Modularity::getVendorPath('operations/' . $this->getDatePrefix() . '_modularity_' . $snakeName . '_operation.php');

        File::put($path, $content);

        $this->info("Operation created successfully: {$path}");

        return 0;
    }

    protected function getDatePrefix(): string
    {
        return Carbon::now()->format('Y_m_d_His');
    }
}
