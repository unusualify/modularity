@php
    $appLocale = app()->getLocale();
    $laravelLocalizationLocale = $appLocale;

    if (class_exists(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::class)) {
        try {
            $laravelLocalizationLocale = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale();
        } catch (\Throwable) {
        }
    }

    $navLabel = __('warmup.navigation.chrome_label');
@endphp
<div
    data-locale="{{ $appLocale }}"
    data-laravel-localization-locale="{{ $laravelLocalizationLocale }}"
    data-nav-label="{{ $navLabel }}"
>warmup-locale-chrome-body</div>
