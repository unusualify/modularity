<?php

namespace Unusualify\Modularity\Http\ViewComposers;

use Illuminate\Contracts\View\View;

class Localization
{
    /**
     * Create a new profile composer.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['modularityLocalization' => get_modularity_localization_config()]);
    }
}
