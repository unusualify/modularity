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

        $name = snakeCase( config(unusualBaseKey() . '.name') );

        // $currentLang = Lang::get("{$name}::lang", [], config(unusualBaseKey() . '.locale'));
        $currentLang = Lang::get('*', [], config(unusualBaseKey() . '.locale'));

        // $fallbackLang = Lang::get("{$name}::lang", [], config(unusualBaseKey() . '.fallback_locale', 'en'));
        $fallbackLang = Lang::get('*', [], config(unusualBaseKey() . '.fallback_locale', 'en'));

        $lang = array_replace_recursive($fallbackLang, $currentLang);

        $unusualLocalization = [
            'locale' => config(unusualBaseKey() . '.locale'),
            'fallback_locale' => config(unusualBaseKey() . '.fallback_locale', 'en'),
            'lang' => $lang
        ];

        $view->with(['unusualLocalization' => $unusualLocalization]);
    }
}
