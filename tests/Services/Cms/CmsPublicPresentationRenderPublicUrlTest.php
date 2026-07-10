<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Modules\Cms\Entities\Page;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\CmsPublicSiteUrl;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicPresentationRenderPublicUrlTest extends TestCase
{
    private string $viewPath;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicPresentationItemCache::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        $this->viewPath = sys_get_temp_dir() . '/modularous-presentation-url-test-' . uniqid('', true);
        mkdir($this->viewPath, 0777, true);

        file_put_contents(
            $this->viewPath . '/url_probe.blade.php',
            <<<'BLADE'
<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="{{ asset('favicon.ico') }}">
<link rel="canonical" href="{{ url('/sample-page') }}">
</head>
<body>probe</body>
</html>
BLADE
        );

        View::addLocation($this->viewPath);

        Config::set('app.url', 'http://frontend.b2press.test');
        Config::set('modularous.cms_routing.canonical_host', 'frontend.b2press.test');
        Config::set('modularous.cms_routing.public_front_route_domain', null);
        Config::set('modularous.cache.enabled', false);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->viewPath);
        parent::tearDown();
    }

    /** @test */
    public function run_with_forced_public_root_url_uses_frontend_host_from_admin_request(): void
    {
        $this->app->instance('request', Request::create('http://cms.b2press.test/admin/cache/warm', 'GET'));

        $html = CmsPublicSiteUrl::runWithForcedPublicRootUrl(function (): string {
            return (string) view('url_probe')->render();
        });

        $this->assertStringContainsString('http://frontend.b2press.test/favicon.ico', $html);
        $this->assertStringContainsString('http://frontend.b2press.test/sample-page', $html);
        $this->assertStringNotContainsString('cms.b2press.test', $html);
    }

    /** @test */
    public function presentation_cache_render_uses_frontend_host_when_request_is_admin(): void
    {
        $this->app->instance('request', Request::create('http://cms.b2press.test/admin/cache/warm', 'GET'));

        $item = new Page;
        $item->id = 901;

        $resolved = CmsPublicPresentationItemCache::resolvePresentationHtml(
            'PrimaryPage',
            'Home',
            $item,
            'url_probe',
            ['item' => $item],
            'en',
            bypassSwr: true,
            normalizedPath: '/sample-page',
        );

        $html = (string) ($resolved['html'] ?? '');

        $this->assertStringContainsString('http://frontend.b2press.test/favicon.ico', $html);
        $this->assertStringContainsString('http://frontend.b2press.test/sample-page', $html);
        $this->assertStringNotContainsString('cms.b2press.test', $html);
    }

    /** @test */
    public function presentation_cache_render_keeps_frontend_host_on_live_public_request(): void
    {
        $this->app->instance('request', Request::create('http://frontend.b2press.test/sample-page', 'GET'));

        $item = new Page;
        $item->id = 902;

        $resolved = CmsPublicPresentationItemCache::resolvePresentationHtml(
            'PrimaryPage',
            'Home',
            $item,
            'url_probe',
            ['item' => $item],
            'en',
            bypassSwr: true,
            normalizedPath: '/sample-page',
        );

        $html = (string) ($resolved['html'] ?? '');

        $this->assertStringContainsString('http://frontend.b2press.test/favicon.ico', $html);
        $this->assertStringNotContainsString('cms.b2press.test', $html);
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($directory);
    }
}
