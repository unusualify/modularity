<?php

namespace OoBook\CRM\Base\Http\Controllers;

use OoBook\CRM\Base\Exceptions\Handler as TwillHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        if (unusualConfig('bind_exception_handler', false)) {
            App::singleton(ExceptionHandler::class, TwillHandler::class);
        }
    }

    /**
     * Attempts to unset the given middleware.
     *
     * @param string $middleware
     * @return void
     */
    public function removeMiddleware($middleware)
    {
        if (($key = array_search($middleware, Arr::pluck($this->middleware, 'middleware'))) !== false) {
            unset($this->middleware[$key]);
        }
    }
}
