<?php

namespace Modules\Cms\Console;

use Illuminate\Console\Command;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;

class CacheCmsPublicUrlRegistryCommand extends Command
{
    protected $signature = 'cms:public-url-registry:cache
                            {--routes : Warm front route registration snapshots only}
                            {--sitemap : Rebuild and commit sitemap XML cache only}';

    protected $description = 'Warm CMS public URL registry caches (front route registration, sitemap from UrlRoute).';

    public function handle(CmsPublicUrlRegistryCacheManager $manager): int
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            $this->warn('CMS features are disabled.');

            return self::FAILURE;
        }

        [$routes, $sitemap] = $this->resolveTargets();

        try {
            $warmed = $manager->cache($routes, $sitemap);
        } catch (\Throwable $e) {
            $this->error('CMS public URL registry cache warm failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        if ($warmed === []) {
            $this->warn('Nothing to warm.');

            return self::SUCCESS;
        }

        foreach ($warmed as $layer) {
            $this->line("  <fg=green>✓</> Cached {$layer}");
        }

        $this->newLine();
        $this->info('CMS public URL registry cache warmed.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function resolveTargets(): array
    {
        $onlyRoutes = (bool) $this->option('routes');
        $onlySitemap = (bool) $this->option('sitemap');

        if (! $onlyRoutes && ! $onlySitemap) {
            return [true, true];
        }

        return [$onlyRoutes, $onlySitemap];
    }
}
