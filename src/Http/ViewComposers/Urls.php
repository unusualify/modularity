<?php

namespace Unusualify\Modularity\Http\ViewComposers;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

class Urls
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct() {}

    /**
     * Binds data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $urls = [
            'profileShow' => route(Route::hasAdmin('profile.show')),
            'profileUpdate' => route(Route::hasAdmin('profile.update')),
        ];

        $view->with(compact('urls'));
    }
}
