<?php

namespace Unusualify\Modularous\Http\Middleware\Concerns;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;

/**
 * Oturum bittiğinde:
 * - Inertia isteği (X-Inertia header): 409 + X-Inertia-Location → Inertia tam sayfa login'e gider.
 * - Ajax (XMLHttpRequest): 401 JSON (`login_url`, `redirect`).
 * - Normal istek: Laravel'in varsayılanı (302).
 *
 * AuthenticationException iki farklı yoldan ulaşabilir:
 * 1. Exception olarak fırlatılır → catch (AuthenticationException) yakalar.
 * 2. Laravel pipeline içinde render edilerek RedirectResponse(302) olarak döner
 *    (pipeline'ın dahili exception handling'i devreye girdiğinde) → response kontrolü yakalar.
 */
trait HandlesUnauthenticatedInertiaAndAjax
{
    /**
     * @param string[] ...$guards
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
        try {
            $response = parent::handle($request, $next, ...$guards);
        } catch (AuthenticationException $e) {
            // Prefer Modularous fallback — AuthenticationException::redirectTo() may invoke
            // Laravel's global redirectUsing callback (route('login')), which apps often lack.
            $loginUrl = $this->resolveUnauthenticatedLoginUrl();

            if ($request->header('X-Inertia')) {
                return response('', 409)->withHeaders([
                    'X-Inertia-Location' => $loginUrl ?: '/',
                ]);
            }

            // Echo /broadcasting/auth sends Accept: application/json but may omit X-Requested-With.
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'login_url' => $loginUrl,
                    'redirect' => (bool) $loginUrl,
                ], 401);
            }

            throw $e;
        }

        // Pipeline bazen AuthenticationException'ı render edip RedirectResponse(302) olarak döndürür.
        // Bu durumu Inertia/AJAX istekleri için de dönüştür.
        if ($response instanceof RedirectResponse && $this->isLoginRedirectResponse($response)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->withHeaders([
                    'X-Inertia-Location' => $response->getTargetUrl(),
                ]);
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => __('Unauthenticated.'),
                    'login_url' => $response->getTargetUrl(),
                    'redirect' => true,
                ], 401);
            }
        }

        return $response;
    }

    /**
     * Redirect response'un login sayfasına yönlendirip yönlendirmediğini kontrol eder.
     * /login, /admin/login, /system/login gibi "login" ile biten path'leri yakalar.
     */
    protected function isLoginRedirectResponse(RedirectResponse $response): bool
    {
        if (! in_array($response->getStatusCode(), [301, 302, 303], true)) {
            return false;
        }

        $path = trim(parse_url($response->getTargetUrl(), PHP_URL_PATH) ?? '', '/');

        return str_ends_with($path, 'login');
    }

    protected function fallbackLoginUrlForUnauthenticated(): string
    {
        return route('login');
    }

    /**
     * Resolve a safe login URL without tripping Laravel's default route('login') callback.
     */
    protected function resolveUnauthenticatedLoginUrl(): ?string
    {
        try {
            $url = $this->fallbackLoginUrlForUnauthenticated();

            return filled($url) ? $url : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
