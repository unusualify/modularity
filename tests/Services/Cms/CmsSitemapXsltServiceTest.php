<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Services\CmsSitemapXsltService;
use Unusualify\Modularous\Tests\TestCase;

class CmsSitemapXsltServiceTest extends TestCase
{
    public function test_transform_produces_html_table_for_minimal_urlset(): void
    {
        if (! extension_loaded('xsl')) {
            $this->markTestSkipped('ext-xsl is required');
        }

        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
  <url>
    <loc>https://example.test/about</loc>
    <lastmod>2026-01-01T00:00:00+00:00</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.5</priority>
  </url>
</urlset>
XML;

        $html = $this->app->make(CmsSitemapXsltService::class)->transformXmlToHtml($xml);

        $this->assertIsString($html);
        $this->assertStringContainsString('XML Sitemap', $html);
        $this->assertStringContainsString('https://example.test/about', $html);
        $this->assertStringContainsString('<table', $html);
    }

    public function test_stylesheet_contents_are_readable(): void
    {
        $xsl = $this->app->make(CmsSitemapXsltService::class)->stylesheetContents();
        $this->assertIsString($xsl);
        $this->assertStringContainsString('xsl:stylesheet', $xsl);
    }
}
