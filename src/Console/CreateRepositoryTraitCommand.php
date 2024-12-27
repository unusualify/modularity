<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;

class CreateRepositoryTraitCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:create:repository:trait {name}';

    protected $aliases = [
        'mod:c:repo:trait',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Repository trait';

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
        // handle command
        $name = $this->argument('name');
        $studlyName = Str::studly($name);

        $replacements = [
            'STUDLY_NAME' => $studlyName,
        ];

        $content = (new Stub('/classes/repository-trait.stub', $replacements))->render();

        $path = base_path(unusualConfig('vendor_path') . '/src/Repositories/Traits/' . $studlyName . 'Trait.php');

        File::put($path, $content);

        return 0;
    }
}
