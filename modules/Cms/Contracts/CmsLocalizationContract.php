<?php

namespace Modules\Cms\Contracts;

use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Support\CmsFrontPath;

/**
 * Bridges CMS URL features ({@see UrlRoute}, slugs, {@see CmsPublicModelResolver})
 * with a concrete localization stack (typically {@code mcamara/laravel-localization}) or translatable fallbacks.
 *
 * Implementations are resolved from the container as a singleton; higher layers (e.g. SiteSetting) can decorate via
 * {@see CmsLocalizationOverrideProviderInterface}.
 */
interface CmsLocalizationContract
{
    /**
     * Identifier for diagnostics: {@code mcamara}, {@code translatable}, etc.
     */
    public function driver(): string;

    /**
     * Locale codes allowed as the first path segment after {@see CmsFrontPath::innerNormalizedPath()}.
     * Sorted longest-first (e.g. {@code en-gb} before {@code en}).
     *
     * @return list<string>
     */
    public function pathSegmentLocales(): array;

    /**
     * Rich locale list for admin / language switcher UIs (native names, script, regional).
     *
     * @return array<string, array<string, mixed>>
     */
    public function supportedLocalesMeta(): array;

    public function defaultLocale(): string;

    /**
     * When true, default locale URLs omit the leading {@code /{locale}} segment (aligned with mcamara's
     * {@code hideDefaultLocaleInURL} when that driver is active).
     */
    public function hideDefaultLocaleInUrl(): bool;

    /**
     * Build a localized URL for a path or absolute URL (wraps mcamara {@code getLocalizedURL} when available).
     */
    public function localizeUrl(?string $url = null, ?string $locale = null): string;

    /**
     * Strip locale prefix from a URL/path (wraps mcamara {@code getNonLocalizedURL} when available).
     */
    public function stripLocaleFromUrl(?string $url = null): string;

    public function applyLocaleToApplication(string $locale): void;
}
