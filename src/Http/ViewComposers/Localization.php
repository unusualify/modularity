<?php

namespace Unusualify\Modularity\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Lang;

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

        $name = snakeCase(modularityConfig('name'));

        // $currentLang = Lang::get("{$name}::lang", [], modularityConfig('locale'));
        $currentLang = Lang::get('*', [], modularityConfig('locale'));

        // $fallbackLang = Lang::get("{$name}::lang", [], modularityConfig('fallback_locale', 'en'));
        $fallbackLang = Lang::get('*', [], modularityConfig('fallback_locale', 'en'));

        $lang = array_replace_recursive($fallbackLang, $currentLang);

        $modularityLocalization = [
            'locale' => modularityConfig('locale'),
            'fallback_locale' => modularityConfig('fallback_locale', 'en'),
            'lang' => $lang,
        ];

        $view->with(['modularityLocalization' => $modularityLocalization]);
    }
}
