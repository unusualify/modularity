<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Http\Response;
use Modules\Cms\Services\CmsSitemapXsltService;

/**
 * Serves the browser XSLT for {@see PublicSitemapController} ({@code /sitemap.xsl}).
 * Cosmetic only — crawlers ignore the stylesheet processing instruction.
 */
final class PublicSitemapXslController
{
    public function __invoke(CmsSitemapXsltService $xslt): Response
    {
        $body = $xslt->stylesheetContents();
        if ($body === null) {
            abort(404);
        }

        return response($body, 200, [
            'Content-Type' => 'text/xsl; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
