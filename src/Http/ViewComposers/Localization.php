<?php

namespace OoBook\CRM\Base\Http\ViewComposers;

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
        $name = lowerName( config('base.name') );

        $currentLang = Lang::get("{$name}::lang", [], config('base.locale'));
        $fallbackLang = Lang::get("{$name}::lang", [], config('base.fallback_locale', 'en'));
        $lang = array_replace_recursive($fallbackLang, $currentLang);

        $unusualLocalization = [
            'locale' => config('base.locale'),
            'fallback_locale' => config('base.fallback_locale', 'en'),
            'lang' => $lang
        ];

        $view->with(['unusualLocalization' => $unusualLocalization]);
    }
}
