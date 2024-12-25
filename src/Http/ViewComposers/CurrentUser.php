<?php

namespace Unusualify\Modularity\Http\ViewComposers;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;

class CurrentUser
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    /**
     * Binds data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $currentUser = $this->authFactory->guard('unusual_users')->user();

        if ($currentUser) {
            $currentUser = get_user_profile($currentUser);
        }

        $view->with(compact('currentUser'));
    }
}
