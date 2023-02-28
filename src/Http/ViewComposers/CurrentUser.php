<?php

namespace Unusual\CRM\Base\Http\ViewComposers;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;

class CurrentUser
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    /**
     * @param AuthFactory $authFactory
     */
    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    /**
     * Binds data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $currentUser = $this->authFactory->guard('unusual_users')->user();

        $view->with(compact('currentUser'));
    }
}
