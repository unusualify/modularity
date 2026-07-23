<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Http\Response;
use Modules\Cms\Jobs\RebuildCmsSitemapJob;
use Modules\Cms\Services\CmsSitemapCacheService;

/**
 * Serves the last **committed** sitemap from cache; rebuild via {@see RebuildCmsSitemapJob} or
 * `cms:sitemap:rebuild` artisan.
 */
final class PublicSitemapController
{
    private const STYLESHEET_PI = '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>';

    public function __invoke(CmsSitemapCacheService $cache): Response
    {
        $body = $this->withStylesheetPi($cache->getCommittedXml());

        return response($body, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'all',
        ]);
    }

    /**
     * Ensure browser XSLT PI is present (safe for caches committed before stylesheet support).
     */
    private function withStylesheetPi(string $xml): string
    {
        if (str_contains($xml, 'xml-stylesheet')) {
            return $xml;
        }

        if (preg_match('/<\?xml[^?]*\?>/', $xml, $m, PREG_OFFSET_CAPTURE) === 1) {
            $end = $m[0][1] + strlen($m[0][0]);

            return substr($xml, 0, $end) . "\n" . self::STYLESHEET_PI . substr($xml, $end);
        }

        return self::STYLESHEET_PI . "\n" . $xml;
    }
}
