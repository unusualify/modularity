<?php

namespace Unusualify\Modularity\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * Get the view used to render HTTP exceptions.
     *
     * @return string
     */
    protected function getHttpExceptionView(HttpExceptionInterface $e)
    {
        $usesAdminPath = !empty(unusualConfig('admin_app_path'));
        $adminAppUrl = unusualConfig('admin_app_url', config('app.url'));

        $isSubdomainAdmin = !$usesAdminPath && Str::contains(Request::url(), $adminAppUrl);
        $isSubdirectoryAdmin = $usesAdminPath && Str::startsWith(Request::path(), unusualConfig('admin_app_path'));

        return $this->getModularityErrorView($e->getStatusCode(), !$isSubdomainAdmin && !$isSubdirectoryAdmin);
    }

    /**
     * Get the Twill error view used to render a specified HTTP status code.
     *
     * @param  integer $statusCode
     * @return string
     */
    protected function getModularityErrorView($statusCode, $frontend = false)
    {
        if ($frontend) {
            $view = unusualConfig('frontend.views_path') . ".errors.$statusCode";

            return view()->exists($view) ? $view : "errors::{$statusCode}";
        }

        $view = "modularity.errors.$statusCode";

        return view()->exists($view) ? $view : unusualBaseKey() . "::errors.$statusCode";
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json($exception->errors(), $exception->status);
    }
}
