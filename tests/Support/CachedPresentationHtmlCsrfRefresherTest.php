<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Support;

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Modules\Cms\Support\CachedPresentationHtmlCsrfRefresher;
use Unusualify\Modularous\Tests\TestCase;

class CachedPresentationHtmlCsrfRefresherTest extends TestCase
{
    /** @test */
    public function it_leaves_html_unchanged_when_no_csrf_markers_are_present(): void
    {
        $html = '<!DOCTYPE html><html><body>static page</body></html>';

        $this->assertSame($html, CachedPresentationHtmlCsrfRefresher::refresh($html));
    }

    /** @test */
    public function it_replaces_stale_meta_and_hidden_token_inputs(): void
    {
        $session = app('session.store');
        $session->start();

        $request = Request::create('/submit-press-release', 'GET');
        $request->setLaravelSession($session);

        $freshToken = csrf_token();
        $this->assertNotSame('', $freshToken);

        $html = '<head><meta name="csrf-token" content="stale-meta"></head>'
            . '<form id="pr-submission-form">'
            . '<input type="hidden" name="_token" value="stale-input" autocomplete="off">'
            . '</form>'
            . '<form>'
            . '<input type="hidden" name="_token" value="stale-popup" autocomplete="off">'
            . '</form>';

        $refreshed = CachedPresentationHtmlCsrfRefresher::refresh($html, $request);

        $this->assertStringContainsString('<meta name="csrf-token" content="' . $freshToken . '">', $refreshed);
        $this->assertStringNotContainsString('stale-meta', $refreshed);
        $this->assertStringNotContainsString('stale-input', $refreshed);
        $this->assertStringNotContainsString('stale-popup', $refreshed);
        $this->assertSame(2, mb_substr_count($refreshed, 'value="' . $freshToken . '"'));
    }

    /** @test */
    public function it_leaves_html_unchanged_when_session_cannot_produce_a_token(): void
    {
        $sessionManager = \Mockery::mock(SessionManager::class);
        $sessionManager->shouldReceive('isStarted')->andReturn(true);
        $sessionManager->shouldReceive('token')->andReturn('');
        $this->app->instance('session', $sessionManager);

        $html = '<meta name="csrf-token" content="stale-meta">';

        $this->assertSame($html, CachedPresentationHtmlCsrfRefresher::refresh($html));
    }
}
