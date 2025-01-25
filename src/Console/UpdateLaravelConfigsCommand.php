<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\File;
use Unusualify\Modularity\Facades\Modularity;

use function Laravel\Prompts\confirm;

class UpdateLaravelConfigsCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:update:laravel:configs';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Laravel Configs';

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
        $modularityAuthGuardName = Modularity::getAuthGuardName();
        $modularityAuthProviderName = Modularity::getAuthProviderName();
        // Laravel Auth Configs
        if(blank(config('auth.guards.' . $modularityAuthGuardName))){
            File::replaceInFile(
                "'guards' => [\n",
                <<<CONFIG
                'guards' => [
                        '{$modularityAuthGuardName}' => [
                            'driver' => 'session',
                            'provider' => '{$modularityAuthProviderName}',
                        ],

                CONFIG,
                app()->configPath('auth.php')
            );
        }
        if(blank(config('auth.providers.' . $modularityAuthProviderName))){
            File::replaceInFile(
                "'providers' => [\n",
                <<<CONFIG
                'providers' => [
                        '{$modularityAuthProviderName}' => [
                            'driver' => 'eloquent',
                            'model' => \Unusualify\Modularity\Entities\User::class,
                        ],

                CONFIG,
                app()->configPath('auth.php')
            );
        }
        if(blank(config('auth.passwords.' . $modularityAuthProviderName))){
            $passwordResetTokensTable = modularityConfig('tables.password_reset_tokens', 'password_reset_tokens');
            File::replaceInFile(
                "'passwords' => [\n",
                <<<CONFIG
                'passwords' => [
                        '{$modularityAuthProviderName}' => [
                            'provider' => '{$modularityAuthProviderName}',
                            'table' => '{$passwordResetTokensTable}',
                            'expire' => 60,
                            'throttle' => 60,
                        ],

                CONFIG,
                app()->configPath('auth.php')
            );
        }

        // Laravel Modules Configs
        if(confirm('Do you want to update nwidart/laravel-modules config?', default: false)){
            if(!config('modules.scan.enabled')){
                File::replaceInFile(
                    "'scan' => [\n        'enabled' => false,",
                        <<<'CONFIG'
                    'scan' => [
                            'enabled' => true,
                    CONFIG,
                    app()->configPath('modules.php')
                );
            }
            dd('fs');
            File::replaceInFile(
                "'cache' => [\n        'enabled' => false,\n        'key' => 'laravel-modules',",
                <<<'CONFIG'
                'cache' => [
                        'enabled' => false,
                        'key' => 'modularity',
                CONFIG,
                app()->configPath('modules.php')
            );
            File::replaceInFile(
                "'cache' => [\n        'enabled' => true,\n        'key' => 'laravel-modules',",
                <<<'CONFIG'
                'cache' => [
                        'enabled' => true,
                        'key' => 'modularity',
                CONFIG,
                app()->configPath('modules.php')
            );
            // $scan_paths = config('modules.scan.paths', []);
            // dd($scan_paths, \Unusualify\Modularity\Facades\Modularity::getVendorPath('umodules'));

            // File::replaceInFile(
            //     "'scan' => [\n        'enabled' => true,\n        'paths' => [\n",
            //     <<<'CONFIG'
            //     'scan' => [
            //             'enabled' => true,
            //             'paths' => [
            //                 base_path('vendor/*/*'),
            //                 realpath(__DIR__ . '/../../umodules'),
            //             ],
            //     ],

            //     CONFIG,
            //     app()->configPath('modules.php')
            // );
        }

        $this->info('Laravel Configs updated successfully');
        return 0;
    }
}
