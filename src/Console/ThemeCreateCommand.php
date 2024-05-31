<?php

namespace Unusualify\Modularity\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Support\Stub;

use function Laravel\Prompts\{select, confirm, warning};

class ThemeCreateCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:create:theme';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create custom theme folder.';

    protected $defaultReject = true;

    protected $isAskable = false;

    /**
     * @var Filesystem
     */
    protected $filesystem;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;

        Stub::setBasePath(dirname(__FILE__).'/stubs');
    }

    /**
     * Execute the console command.
     */
    public function handle() : int
    {
        $name = $this->argument('name');

        $success = true;

        if(!file_exists(resource_path('vendor/modularity/themes'))){
            $this->filesystem->makeDirectory(resource_path('vendor/modularity/themes'));
            $this->filesystem->put(resource_path('vendor/modularity/themes/.keep'), '');

        }

        $extendTheme = $this->option('extend');

        $sassPath = base_path(unusualConfig('vendor_path') . '/vue/src/sass/themes/' . $extendTheme);
        $jsPath = base_path(unusualConfig('vendor_path') . '/vue/src/js/config/themes/' . $extendTheme . ".js");

        if( !$extendTheme || !($this->filesystem->exists($sassPath) && $this->filesystem->exists($jsPath))  ){
            warning('Theme name to be extended');
            // $useUnusualTheme = confirm(
            //     label: 'Do you want to use the unusual theme?',
            //     default: true,
            //     yes: 'YES',
            //     no: 'No, select another theme',
            //     // hint: 'Default e-mail address: software-dev@unusualgrowth.com',
            // );
            $themes = builtInModularityThemes();

            $extendTheme = select(
                label: 'Which theme to be extended?',
                options: $themes,
                default: 'unusual'
            );

            $sassPath = base_path(unusualConfig('vendor_path') . '/vue/src/sass/themes/' . $extendTheme);
            $jsPath = base_path(unusualConfig('vendor_path') . '/vue/src/js/config/themes/' . $extendTheme . ".js");
        }

        $destination = resource_path('vendor/modularity/themes/' . $name );

        $this->filesystem->copyDirectory(
            $sassPath, $destination . '/sass'
        );

        $this->filesystem->copy(
            $jsPath, $destination . '/' . $name . '.js'
        );

        $this->info('New theme has been created');


        return $success ? 0 : E_ERROR;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of theme to be created.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge([
            ['extend', null, InputOption::VALUE_OPTIONAL, 'The custom extendable theme name.', null],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
        ]);
    }
}
