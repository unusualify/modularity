<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Unusualify\Modularous\Facades\SiteSettings;
use Unusualify\Modularous\Support\ModularousCacheLogger;

/**
 * Applies visitor-facing presentation context during admin warmup or cache render:
 * application locale, optional mcamara locale, route URL defaults, public site root URL,
 * and frontend SiteSettings (CmsSettings) so queue/console warm matches HTTP visitors.
 */
final class CmsPublicPresentationWarmupContext
{
    /**
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public static function run(string $locale, callable $callback): mixed
    {
        $snapshot = self::captureState();

        try {
            self::apply($locale);

            $run = static fn () => CmsPublicSiteUrl::runWithForcedPublicRootUrl($callback);

            // Queue / artisan warm runs in console → SiteSettings defaults to SystemSettings.
            // Force CMS layer so Blade (e.g. site.logo.frontend) matches public HTTP renders.
            // Skip when Cms module has not bound site.settings (e.g. isolated package tests).
            if (app()->bound('site.settings')) {
                return SiteSettings::whileFrontend($run);
            }

            return $run();
        } finally {
            self::restore($snapshot);
        }
    }

    public static function apply(string $locale): void
    {
        if (
            interface_exists(CmsLocalizationContract::class)
            && app()->bound(CmsLocalizationContract::class)
        ) {
            app(CmsLocalizationContract::class)->applyLocaleToApplication($locale);
        } else {
            app()->setLocale($locale);
        }

        Lang::setLocale($locale);

        if (class_exists(LaravelLocalization::class)) {
            try {
                LaravelLocalization::setLocale($locale);
            } catch (\Throwable) {
            }
        }

        URL::defaults(['locale' => $locale]);

        self::logAppliedLocaleContext($locale);
    }

    private static function logAppliedLocaleContext(string $requestedLocale): void
    {
        if (! class_exists(ModularousCacheLogger::class)) {
            return;
        }

        $laravelLocalizationLocale = null;
        if (class_exists(LaravelLocalization::class)) {
            try {
                $laravelLocalizationLocale = LaravelLocalization::getCurrentLocale();
            } catch (\Throwable) {
            }
        }

        /** @var UrlGenerator $url */
        $url = app(UrlGenerator::class);

        ModularousCacheLogger::info('cache.warmup.presentation_item.locale_context', [
            'requested_locale' => $requestedLocale,
            'app_locale' => app()->getLocale(),
            'lang_locale' => Lang::getLocale(),
            'laravel_localization_locale' => $laravelLocalizationLocale,
            'url_default_locale' => $url->getDefaultParameters()['locale'] ?? null,
        ]);
    }

    /**
     * @return array{appLocale: string, urlDefaults: array<string, mixed>, mcamaraLocale: ?string}
     */
    private static function captureState(): array
    {
        /** @var UrlGenerator $url */
        $url = app(UrlGenerator::class);

        $mcamaraLocale = null;
        if (class_exists(LaravelLocalization::class)) {
            try {
                $current = LaravelLocalization::getCurrentLocale();
                if (is_string($current) && $current !== '') {
                    $mcamaraLocale = $current;
                }
            } catch (\Throwable) {
            }
        }

        return [
            'appLocale' => app()->getLocale(),
            'urlDefaults' => $url->getDefaultParameters(),
            'mcamaraLocale' => $mcamaraLocale,
        ];
    }

    /**
     * @param array{appLocale: string, urlDefaults: array<string, mixed>, mcamaraLocale: ?string} $snapshot
     */
    private static function restore(array $snapshot): void
    {
        if (
            interface_exists(CmsLocalizationContract::class)
            && app()->bound(CmsLocalizationContract::class)
        ) {
            app(CmsLocalizationContract::class)->applyLocaleToApplication($snapshot['appLocale']);
        } else {
            app()->setLocale($snapshot['appLocale']);
        }

        /** @var UrlGenerator $url */
        $url = app(UrlGenerator::class);
        self::setUrlDefaultParameters($url, $snapshot['urlDefaults']);

        if ($snapshot['mcamaraLocale'] !== null && class_exists(LaravelLocalization::class)) {
            try {
                LaravelLocalization::setLocale($snapshot['mcamaraLocale']);
            } catch (\Throwable) {
            }
        }
    }

    /**
     * @param array<string, mixed> $defaults
     */
    private static function setUrlDefaultParameters(UrlGenerator $url, array $defaults): void
    {
        $routeUrl = (new \ReflectionMethod($url, 'routeUrl'))->invoke($url);
        (new \ReflectionProperty($routeUrl, 'defaultParameters'))->setValue($routeUrl, $defaults);
    }
}
