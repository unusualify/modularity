<?php

namespace Modules\Cms\Support;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Cache;

/**
 * {@see AboutCommand} rows for CMS public URL registry caches (ParentSegment / UrlRoute revisions,
 * front route registration snapshots, committed sitemap XML).
 */
final class CmsPublicUrlRegistryAboutReporter
{
    /**
     * @return array<string, mixed> Label => value (string or AboutCommand format closure).
     */
    public function report(): array
    {
        $formatEnabled = static fn (bool $value) => AboutCommand::format(
            $value,
            console: static fn (bool $enabled) => $enabled
                ? '<fg=yellow;options=bold>ENABLED</>'
                : 'OFF',
        );

        $formatCached = static fn (bool $value) => AboutCommand::format(
            $value,
            console: static fn (bool $cached) => $cached
                ? '<fg=green;options=bold>CACHED</>'
                : '<fg=yellow;options=bold>NOT CACHED</>',
        );

        $report = [];

        if ((bool) modularousConfig('cms_routing.auto_register_public_front', true)) {
            $report['Public Front'] = value($formatEnabled, true);
            $report['Universal Front'] = value(
                $formatEnabled,
                (bool) modularousConfig('cms_routing.universal_cms_public_front', true),
            );

            if ((bool) modularousConfig('cms_routing.front_route_registration_cache_enabled', true)) {
                $report['Front Routes'] = value(
                    $formatCached,
                    CmsFrontRouteRegistrationCache::hasPersistentSnapshot(),
                );
            } else {
                $report['Front Routes'] = 'OFF';
            }

            $report['Registry Store'] = (string) modularousConfig(
                'cms_routing.public_url_registry_cache_store',
                'file',
            );
            $report['ParentSegment Revision'] = CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision();
            $report['UrlRoute Revision'] = CmsPublicUrlRegistryCoordinator::urlRouteRegistryRevision();
        }

        if ((bool) modularousConfig('cms_sitemap.route_enabled', true)) {
            $report['Sitemap'] = value($formatCached, $this->sitemapIsCached());
        }

        return $report;
    }

    private function sitemapIsCached(): bool
    {
        $key = (string) modularousConfig('cms_sitemap.cache_key', 'modularous_cms_sitemap.committed_v1');
        $raw = Cache::get($key);

        return is_string($raw) && $raw !== '';
    }
}
