<?php

namespace Modules\Cms\Console;

use Illuminate\Console\Command;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;

class ClearCmsPublicUrlRegistryCacheCommand extends Command
{
    protected $signature = 'cms:public-url-registry:clear
                            {--revisions : Clear ParentSegment / UrlRoute revision counters only}
                            {--routes : Clear front route registration snapshots only}
                            {--sitemap : Clear committed sitemap XML cache only}';

    protected $description = 'Clear CMS public URL registry caches (ParentSegment, UrlRoute revisions, front routes, sitemap).';

    public function handle(CmsPublicUrlRegistryCacheManager $manager): int
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            $this->warn('CMS features are disabled.');

            return self::FAILURE;
        }

        [$revisions, $routes, $sitemap] = $this->resolveTargets();

        $cleared = $manager->clear($revisions, $routes, $sitemap);

        if ($cleared === []) {
            $this->warn('Nothing to clear.');

            return self::SUCCESS;
        }

        foreach ($cleared as $layer) {
            $this->line("  <fg=green>✓</> Cleared {$layer}");
        }

        $this->newLine();
        $this->info('CMS public URL registry cache cleared.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: bool, 1: bool, 2: bool}
     */
    private function resolveTargets(): array
    {
        $onlyRevisions = (bool) $this->option('revisions');
        $onlyRoutes = (bool) $this->option('routes');
        $onlySitemap = (bool) $this->option('sitemap');

        if (! $onlyRevisions && ! $onlyRoutes && ! $onlySitemap) {
            return [true, true, true];
        }

        return [$onlyRevisions, $onlyRoutes, $onlySitemap];
    }
}
