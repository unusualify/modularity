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
        if (blank(config('auth.guards.' . $modularityAuthGuardName))) {
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
        if (blank(config('auth.providers.' . $modularityAuthProviderName))) {
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
        if (blank(config('auth.passwords.' . $modularityAuthProviderName))) {
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
        if (confirm('Do you want to update nwidart/laravel-modules config?', default: false)) {
            if (! config('modules.scan.enabled')) {
                File::replaceInFile(
                    "'scan' => [\n        'enabled' => false,",
                    <<<'CONFIG'
                    'scan' => [
                            'enabled' => true,
                    CONFIG,
                    app()->configPath('modules.php')
                );
            }

            File::replaceInFile(
                "'cache' => [\n        'enabled' => false",
                <<<'CONFIG'
                'cache' => [
                        'enabled' => env('MODULARITY_CACHE_ENABLED', true)
                CONFIG,
                app()->configPath('modules.php')
            );
            File::replaceInFile(
                "'cache' => [\n        'enabled' => true",
                <<<'CONFIG'
                'cache' => [
                        'enabled' => env('MODULARITY_CACHE_ENABLED', true)
                CONFIG,
                app()->configPath('modules.php')
            );
            File::replaceInFile(
                "'key' => 'laravel-modules',",
                <<<'CONFIG'
                'key' => env('MODULARITY_CACHE_KEY', 'modularity'),
                CONFIG,
                app()->configPath('modules.php')
            );
            File::replaceInFile(
                "'lifetime' => 60,",
                <<<'CONFIG'
                'lifetime' => env('MODULARITY_CACHE_LIFETIME', 600),
                CONFIG,
                app()->configPath('modules.php')
            );
        }

        // Larravel translation config
        if (confirm('Do you want to update joedixon/laravel-translation config?', default: false)) {
            File::replaceInFile(
                "'middleware' => 'web'",
                <<<'CONFIG'
                'middleware' => [
                            'web',
                            'modularity.auth:modularity',
                            'language',
                            'auth',
                            'navigation',
                            'impersonate'
                        ]
                CONFIG,
                app()->configPath('translation.php')
            );
            File::replaceInFile(
                "'ui_url' => 'languages',",
                <<<'CONFIG'
                'ui_url' => 'system-settings/locales',
                CONFIG,
                app()->configPath('translation.php')
            );
            File::replaceInFile(
                "'translation_methods' => ['trans', '__'],",
                <<<'CONFIG'
                'translation_methods' => ['trans', '__', '___'],
                CONFIG,
                app()->configPath('translation.php')
            );
        }

        $this->info('Laravel Configs updated successfully');

        return 0;
    }
}
