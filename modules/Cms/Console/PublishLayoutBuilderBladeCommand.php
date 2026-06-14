<?php

declare(strict_types=1);

namespace Modules\Cms\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Copies filesystem-layout starter Blade stubs into {@code resources/views/vendor/modularous/cms/layout-builder}.
 */
class PublishLayoutBuilderBladeCommand extends Command
{
    protected $signature = 'cms:layout-builder:publish-blades 
                            {--force : Replace existing Blade files without prompting}';

    protected $description = 'Publish filesystem-mode LayoutBuilder Blade stubs to resources/views/vendor/modularous (developer convenience).';

    public function handle(): int
    {
        $stub = dirname(__DIR__) . '/Stubs/layout-builder/vendor/modularous/cms/layout-builder/master.blade.php.stub';
        $targetRelative = 'views/vendor/modularous/cms/layout-builder/master.blade.php';
        $target = resource_path($targetRelative);

        if (! is_file($stub)) {
            $this->error('Stub not found at: ' . $stub);

            return self::FAILURE;
        }

        if (File::exists($target) && ! $this->option('force')) {
            if (! $this->confirm('Target already exists at ' . $targetRelative . '. Overwrite?', false)) {
                $this->info('Cancelled.');

                return self::INVALID;
            }
        }

        File::ensureDirectoryExists(dirname($target));
        File::copy($stub, $target);

        $this->info('Published: ' . $target);
        $this->line('Suggested view name: vendor.modularous.cms.layout-builder.master');
        $this->comment('Run php artisan view:clear after deploying view changes.');

        return self::SUCCESS;
    }
}
