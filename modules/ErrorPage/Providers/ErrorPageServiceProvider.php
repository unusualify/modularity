<?php

declare(strict_types=1);

namespace Modules\ErrorPage\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Support\ServiceProvider;
use Modules\ErrorPage\Console\CreateErrorPageDefaultsCommand;
use Modules\ErrorPage\Support\ErrorPageRenderer;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Unusualify\Modularous\Facades\Modularous;

class ErrorPageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ErrorPageRenderer::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateErrorPageDefaultsCommand::class,
            ]);
        }

        $this->registerExceptionRendering();
    }

    private function registerExceptionRendering(): void
    {
        $this->callAfterResolving(ExceptionHandlerContract::class, function (ExceptionHandlerContract $handler): void {
            if (! method_exists($handler, 'renderable')) {
                return;
            }

            $handler->renderable(function (HttpExceptionInterface $e, $request) {
                if (Modularous::isPanelUrl($request->fullUrl())) {
                    return null;
                }

                if (! modularousConfig('cms_features.error_pages_enabled', true)) {
                    return null;
                }

                $status = $e->getStatusCode();
                if (! in_array($status, [403, 404, 500], true)) {
                    return null;
                }

                if (! $this->app->bound(ErrorPageRenderer::class)) {
                    return null;
                }

                return $this->app->make(ErrorPageRenderer::class)->toResponse($status, $request);
            });
        });
    }
}
