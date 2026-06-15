<?php

namespace Modules\Cms\Services;

use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Support\CmsPathLocale;

class CanonicalUrlResolver implements CanonicalUrlResolverInterface
{
    public function resolve(?string $host, string $path, ?string $locale = null, array $options = []): array
    {
        $canonicalHost = (string) ($options['canonical_host'] ?? modularousConfig('cms_routing.canonical_host', request()->getHost()));
        $defaultLocale = (string) ($options['default_locale'] ?? modularousConfig('cms_routing.default_locale', app()->getLocale()));
        $hideDefaultLocale = (bool) ($options['hide_default_locale_segment'] ?? modularousConfig('cms_routing.hide_default_locale_segment', false));
        $redirectToCanonical = (bool) ($options['redirect_to_canonical'] ?? modularousConfig('cms_routing.redirect_to_canonical', true));

        $locale = $locale ?: $defaultLocale;
        $normalizedPath = $this->normalizePath($path);

        $localePrefix = ($locale === $defaultLocale && $hideDefaultLocale)
            ? ''
            : '/' . trim($locale, '/');

        $withoutLocale = $this->stripLocalePrefix(
            $normalizedPath,
            array_values(array_unique(array_filter([$locale, $defaultLocale, ...CmsPathLocale::pathSegmentLocales()])))
        );
        $canonicalPath = rtrim($localePrefix . '/' . ltrim($withoutLocale, '/'), '/');
        $canonicalPath = $canonicalPath === '' ? '/' : $canonicalPath;
        $canonicalUrl = 'https://' . $canonicalHost . $normalizedPath;

        $effectiveHost = $host ?: request()->getHost();
        $incomingUrl = 'https://' . $effectiveHost . $normalizedPath;
        $shouldRedirect = $redirectToCanonical && $incomingUrl !== $canonicalUrl;

        return [
            'canonical_url' => $canonicalUrl,
            'canonical_path' => $canonicalPath,
            'normalized_path' => $normalizedPath,
            'should_redirect' => $shouldRedirect,
            'redirect_to' => $shouldRedirect ? $canonicalUrl : null,
            'locale' => $locale,
        ];
    }

    public function normalizePath(string $path): string
    {
        $path = trim($path);
        $path = '/' . ltrim($path, '/');

        if (modularousConfig('cms_seo.canonical.force_lowercase_path', true)) {
            $path = mb_strtolower($path);
        }

        $path = preg_replace('#/+#', '/', $path) ?: '/';

        if (modularousConfig('cms_seo.canonical.trim_trailing_slash', true) && mb_strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    public function normalizedPathRegistryLookupVariants(string $pathKey): array
    {
        $canonical = $this->normalizePath($pathKey);
        $variants = [$canonical];

        if ($canonical !== '/' && $canonical !== '') {
            $withoutSlash = ltrim($canonical, '/');
            if ($withoutSlash !== '' && $withoutSlash !== $canonical) {
                $variants[] = $withoutSlash;
            }
        }

        return array_values(array_unique($variants));
    }

    protected function stripLocalePrefix(string $path, array $locales = []): string
    {
        $locales = $locales === [] ? CmsPathLocale::pathSegmentLocales() : $locales;

        foreach ($locales as $locale) {
            $needle = '/' . trim((string) $locale, '/');
            if ($path === $needle) {
                return '/';
            }

            if (str_starts_with($path, $needle . '/')) {
                return mb_substr($path, mb_strlen($needle));
            }
        }

        return $path;
    }
}
