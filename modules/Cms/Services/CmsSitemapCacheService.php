<?php

namespace Modules\Cms\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Committed sitemap XML is stored in the default cache store (use {@code rememberForever} on commit).
 * Public responses always read the last committed value so a failed build never blanks the live sitemap.
 */
final class CmsSitemapCacheService
{
    public function getCommittedXml(): string
    {
        $key = (string) modularousConfig('cms_sitemap.cache_key', 'modularous_cms_sitemap.committed_v1');
        $raw = Cache::get($key);

        if (is_string($raw) && $raw !== '') {
            return $raw;
        }

        if ((bool) modularousConfig('cms_sitemap.build_on_cache_miss', false)) {
            $xml = app(CmsSitemapBuildService::class)->buildXml();
            $this->commit($xml);

            return $xml;
        }

        return $this->emptyUrlset();
    }

    public function commit(string $xml): void
    {
        $key = (string) modularousConfig('cms_sitemap.cache_key', 'modularous_cms_sitemap.committed_v1');
        Cache::forever($key, $xml);
    }

    public function forget(): void
    {
        $key = (string) modularousConfig('cms_sitemap.cache_key', 'modularous_cms_sitemap.committed_v1');
        Cache::forget($key);
    }

    public function emptyUrlset(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
</urlset>

XML;
    }
}
