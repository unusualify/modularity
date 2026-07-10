<?php

namespace Modules\Cms\Localization;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Support\CmsPathLocale;

/**
 * Uses {@see LaravelLocalization} for locale lists, URL helpers, and hide-default behaviour.
 */
final class McamaraCmsLocalizationAdapter implements CmsLocalizationContract
{
    public function __construct(
        private CanonicalUrlResolverInterface $canonical,
    ) {}

    public function driver(): string
    {
        return 'mcamara';
    }

    public function pathSegmentLocales(): array
    {
        $configured = modularousConfig('cms_routing.path_segment_locales');
        if (is_array($configured) && $configured !== []) {
            return $this->sortedLongestFirst(array_values(array_unique(array_filter(array_map('strval', $configured)))));
        }

        $mcamaraKeys = null;

        try {
            $maybe = LaravelLocalization::getSupportedLanguagesKeys();
            if (is_array($maybe) && $maybe !== []) {
                $mcamaraKeys = $maybe;
            }
        } catch (\Throwable) {
        }

        return CmsPathLocale::mergeMcamaraKeysWithSiteLocales($mcamaraKeys);
    }

    public function supportedLocalesMeta(): array
    {
        try {
            $locales = LaravelLocalization::getSupportedLocales();
            if (is_array($locales) && $locales !== []) {
                return $locales;
            }
        } catch (\Throwable) {
        }

        return $this->minimalMetaForKeys($this->pathSegmentLocales());
    }

    public function defaultLocale(): string
    {
        $cms = (string) modularousConfig('cms_routing.default_locale', '');
        if ($cms !== '') {
            return $cms;
        }

        try {
            $fromPackage = config('laravellocalization.locale');
            if (is_string($fromPackage) && $fromPackage !== '') {
                return $fromPackage;
            }
        } catch (\Throwable) {
        }

        return (string) config('app.locale');
    }

    public function hideDefaultLocaleInUrl(): bool
    {
        $source = (string) modularousConfig('cms_routing.localization_hide_default_source', 'mcamara');

        if ($source === 'cms') {
            return (bool) modularousConfig('cms_routing.hide_default_locale_segment', false);
        }

        try {
            $mcamara = (bool) config('laravellocalization.hideDefaultLocaleInURL', false);
            if ($source === 'mcamara') {
                return $mcamara;
            }

            return $mcamara || (bool) modularousConfig('cms_routing.hide_default_locale_segment', false);
        } catch (\Throwable) {
            return (bool) modularousConfig('cms_routing.hide_default_locale_segment', false);
        }
    }

    public function localizeUrl(?string $url = null, ?string $locale = null): string
    {
        try {
            return (string) LaravelLocalization::getLocalizedURL($locale, $url);
        } catch (\Throwable) {
            return (new TranslatableCmsLocalizationAdapter($this->canonical))->localizeUrl($url, $locale);
        }
    }

    public function stripLocaleFromUrl(?string $url = null): string
    {
        try {
            return (string) LaravelLocalization::getNonLocalizedURL($url);
        } catch (\Throwable) {
            return (new TranslatableCmsLocalizationAdapter($this->canonical))->stripLocaleFromUrl($url);
        }
    }

    public function applyLocaleToApplication(string $locale): void
    {
        app()->setLocale($locale);

        try {
            LaravelLocalization::setLocale($locale);
        } catch (\Throwable) {
        }
    }

    /**
     * @param list<string> $keys
     * @return array<string, array<string, mixed>>
     */
    private function minimalMetaForKeys(array $keys): array
    {
        $meta = [];
        foreach ($keys as $locale) {
            $meta[$locale] = [
                'name' => $locale,
                'native' => $locale,
                'script' => 'Latn',
                'regional' => '',
            ];
        }

        return $meta;
    }

    /**
     * @param list<string> $locales
     * @return list<string>
     */
    private function sortedLongestFirst(array $locales): array
    {
        usort($locales, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return $locales;
    }
}
