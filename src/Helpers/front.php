<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousVite;

if (! function_exists('getHost')) {
    /**
     * @param string $file
     * @return string
     */
    function getHost()
    {
        return parse_url(config('app.url'))['host'];
    }
}

if (! function_exists('getModularousDefaultUrls')) {
    /**
     * Get the default urls for the modularous
     *
     * @return array
     */
    function getModularousDefaultUrls()
    {
        return [
            'languages' => route(Route::hasAdmin('api.languages.index')),
            'base_permalinks' => Arr::mapWithKeys(getLocales(), function ($locale, $key) {
                return [$locale => parse_url(config('app.url'), PHP_URL_HOST)];
            }),
        ];
    }
}

if (! function_exists('modularous_svg_symbol_exists')) {
    /**
     * Check whether an SVG <symbol> exists in the Modularous SVG sprite/theme by its short name.
     *
     * Example: modularousIconExists('main-logo-dark') or modularousIconExists('icon--main-logo-dark')
     */
    function modularous_svg_symbol_exists(string $symbolName): bool
    {
        $svgName = str_starts_with($symbolName, 'icon--') ? preg_replace('/^icon--/', '', $symbolName) : $symbolName;
        $normalized = str_starts_with($symbolName, 'icon--') ? $symbolName : 'icon--' . $symbolName;

        static $parsedSymbolIds = null;

        if ($parsedSymbolIds === null) {
            $isRunningHot = ModularousVite::isRunningHot();
            if ($isRunningHot) {
                return file_exists(Modularous::getThemePath("icons/{$svgName}.svg"));
            } else {
                // Prefer the full theme file where <symbol> definitions live; fall back to the sprite wrapper
                $viewName = View::exists('modularous::partials.icons.svg-theme')
                    ? 'modularous::partials.icons.svg-theme'
                    : (View::exists('modularous::partials.icons.svg-sprite') ? 'modularous::partials.icons.svg-sprite' : null);

                if ($viewName === null) {
                    return false;
                }

                try {
                    tap((string) view($viewName)->render(), function ($content) use (&$parsedSymbolIds) {
                        $parsedSymbolIds = [];
                        if (preg_match_all('/<symbol[^>]+id="([^"]+)"/i', $content, $matches) && isset($matches[1])) {
                            $parsedSymbolIds = array_fill_keys($matches[1], true);
                        }
                    });
                } catch (Throwable $e) {
                    return false;
                }
            }

        }

        return isset($parsedSymbolIds[$normalized]);
    }
}

if (! function_exists('get_modularous_logo_symbol')) {
    function get_modularous_logo_symbol(array $symbols)
    {
        return collect($symbols)->first(function ($value, $key) {
            return modularous_svg_symbol_exists($value);
        });
    }
}

if (! function_exists('get_modularous_locale_symbol')) {
    function get_modularous_locale_symbol(string $symbol, $default = null)
    {
        $locale = app()->getLocale();
        $defaults = is_array($default) ? $default : [$default];

        $symbols = [
            "{$symbol}-{$locale}",
            $symbol,
            ...$defaults,
        ];

        return get_modularous_logo_symbol($symbols);
    }
}
