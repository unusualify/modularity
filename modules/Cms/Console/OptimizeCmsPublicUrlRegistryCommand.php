<?php

namespace Modules\Cms\Console;

use Illuminate\Console\Command;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;

class OptimizeCmsPublicUrlRegistryCommand extends Command
{
    protected $signature = 'cms:public-url-registry:optimize';

    protected $description = 'Clear then warm CMS public URL registry caches (deploy helper).';

    public function handle(CmsPublicUrlRegistryCacheManager $manager): int
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            $this->warn('CMS features are disabled.');

            return self::FAILURE;
        }

        $this->info('Clearing CMS public URL registry caches...');
        $manager->clear();

        $this->newLine();
        $this->info('Warming CMS public URL registry caches...');

        try {
            $manager->cache();
        } catch (\Throwable $e) {
            $this->error('Optimize failed during warm: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('CMS public URL registry optimized.');

        return self::SUCCESS;
    }
}
