<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware\Concerns;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Http\Middleware\Concerns\HandlesUnauthenticatedInertiaAndAjax;
use Unusualify\Modularous\Tests\TestCase;

class HandlesUnauthenticatedInertiaAndAjaxTest extends TestCase
{
    /** @test */
    public function it_converts_authentication_exception_for_inertia_and_ajax(): void
    {
        Route::get('/login', fn () => 'login')->name('login');

        $middleware = new class($this->app['auth']) extends Authenticate
        {
            use HandlesUnauthenticatedInertiaAndAjax;

            protected function authenticate($request, array $guards)
            {
                throw new AuthenticationException('Unauthenticated.');
            }
        };

        $inertia = Request::create('/admin', 'GET', [], [], [], [
            'HTTP_X_INERTIA' => 'true',
        ]);
        $inertiaResponse = $middleware->handle($inertia, fn () => response('ok'));
        $this->assertSame(409, $inertiaResponse->getStatusCode());
        $this->assertNotEmpty($inertiaResponse->headers->get('X-Inertia-Location'));

        $ajax = Request::create('/admin', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $ajaxResponse = $middleware->handle($ajax, fn () => response('ok'));
        $this->assertSame(401, $ajaxResponse->getStatusCode());
        $this->assertArrayHasKey('login_url', $ajaxResponse->getData(true));
    }

    /** @test */
    public function it_rethrows_authentication_exception_for_normal_requests(): void
    {
        Route::get('/login', fn () => 'login')->name('login');

        $middleware = new class($this->app['auth']) extends Authenticate
        {
            use HandlesUnauthenticatedInertiaAndAjax;

            protected function authenticate($request, array $guards)
            {
                throw new AuthenticationException('Unauthenticated.');
            }
        };

        $this->expectException(AuthenticationException::class);
        $middleware->handle(Request::create('/admin', 'GET'), fn () => response('ok'));
    }

    /** @test */
    public function it_converts_login_redirect_responses_for_inertia_and_ajax(): void
    {
        $middleware = new HandlesUnauthenticatedRedirectSubject($this->app['auth']);
        $middleware->forcedParentResponse = redirect('/admin/login');

        $this->assertTrue($middleware->exposeIsLoginRedirectResponse(redirect('/admin/login')));
        $this->assertFalse($middleware->exposeIsLoginRedirectResponse(redirect('/dashboard')));
        $badStatus = redirect('/admin/login');
        $badStatus->setStatusCode(200);
        $this->assertFalse($middleware->exposeIsLoginRedirectResponse($badStatus));

        $inertia = Request::create('/admin', 'GET', [], [], [], ['HTTP_X_INERTIA' => 'true']);
        $inertiaResp = $middleware->handle($inertia, fn () => response('ok'));
        $this->assertSame(409, $inertiaResp->getStatusCode());
        $this->assertNotEmpty($inertiaResp->headers->get('X-Inertia-Location'));

        $ajax = Request::create('/admin', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $ajaxResp = $middleware->handle($ajax, fn () => response('ok'));
        $this->assertSame(401, $ajaxResp->getStatusCode());
        $payload = $ajaxResp->getData(true);
        $this->assertTrue($payload['redirect']);
        $this->assertArrayHasKey('login_url', $payload);
    }
}

/**
 * Parent returns a forced RedirectResponse so the trait's post-parent conversion path runs.
 */
class HandlesUnauthenticatedRedirectParent extends Authenticate
{
    public ?RedirectResponse $forcedParentResponse = null;

    public function handle($request, Closure $next, ...$guards): mixed
    {
        return $this->forcedParentResponse ?? $next($request);
    }
}

class HandlesUnauthenticatedRedirectSubject extends HandlesUnauthenticatedRedirectParent
{
    use HandlesUnauthenticatedInertiaAndAjax;

    public function __construct(AuthFactory $auth)
    {
        parent::__construct($auth);
    }

    public function exposeIsLoginRedirectResponse(RedirectResponse $response): bool
    {
        return $this->isLoginRedirectResponse($response);
    }

    protected function fallbackLoginUrlForUnauthenticated(): string
    {
        return '/admin/login';
    }
}
