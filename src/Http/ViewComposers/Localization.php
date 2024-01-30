<?php

namespace Unusualify\Modularity\Http\ViewComposers;

use App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\View\View;

class Localization
{
    /**
     * Create a new profile composer.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {

        $name = snakeCase( unusualConfig('name') );

        // $currentLang = Lang::get("{$name}::lang", [], unusualConfig('locale'));
        $currentLang = Lang::get('*', [], unusualConfig('locale'));

        // $fallbackLang = Lang::get("{$name}::lang", [], unusualConfig('fallback_locale', 'en'));
        $fallbackLang = Lang::get('*', [], unusualConfig('fallback_locale', 'en'));

        $lang = array_replace_recursive($fallbackLang, $currentLang);

        $unusualLocalization = [
            'locale' => unusualConfig('locale'),
            'fallback_locale' => unusualConfig('fallback_locale', 'en'),
            'lang' => $lang
        ];

        $view->with(['unusualLocalization' => $unusualLocalization]);
    }
}
