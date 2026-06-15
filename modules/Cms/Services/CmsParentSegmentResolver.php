<?php

namespace Modules\Cms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Entities\ParentSegment;

/**
 * Resolves shared bindings keyed by module-route model + locale (see {@see ParentSegment}):
 * parent URL path prefix. Presentation shells are {@see PageLayout} / {@see CmsPageLayoutResolver}.
 *
 * When a locale has no enabled binding (missing row or enabled=0), the prefix falls back to the first
 * enabled binding matching `cms_routing.default_locale`, `translatable.fallback_locale`,
 * {@see getLocales()}, then other locales that have bindings — so `tr` can use `pages` while
 * editorial labels still show `pages` / `sayfalar` separately.
 */
final class CmsParentSegmentResolver
{
    public function __construct(
        private CanonicalUrlResolverInterface $canonicalUrlResolver,
    ) {}

    public function enabled(): bool
    {
        return (bool) modularousConfig('cms_parent_segments.enabled', true);
    }

    public function tablesReady(): bool
    {
        return Schema::hasTable((new ParentSegment)->getTable());
    }

    /**
     * The enabled binding row for this model class + locale (same choice rules as prefix resolution), or null.
     */
    public function parentSegmentBindingFor(string $targetClass, string $locale): ?ParentSegment
    {
        if (! $this->enabled() || ! $this->tablesReady()) {
            return null;
        }

        return $this->resolvePreferredEnabledBindingOrFallbackLocale($targetClass, (string) $locale);
    }

    /**
     * Normalized path prefix for this model class + locale (e.g. `/blog`), {@code '/'} when the binding stores a deliberate
     * blank prefix (locale-root homepage), or null when no applicable enabled binding remains.
     */
    public function normalizedPrefixForTargetLocale(string $targetClass, string $locale): ?string
    {
        $binding = $this->parentSegmentBindingFor($targetClass, $locale);

        return $this->normalizedPathFromBinding($binding);
    }

    /**
     * Full public path: optional parent prefix + slug leaf (per-locale slug string).
     */
    public function joinPublicLeafPath(string $targetClass, string $locale, string $slugLeaf): string
    {
        $prefix = $this->normalizedPrefixForTargetLocale($targetClass, $locale);
        $leaf = trim($slugLeaf, '/');
        if ($leaf === '') {
            return $prefix ?? '/';
        }
        if ($prefix === null || $prefix === '' || $prefix === '/') {
            return $this->canonicalUrlResolver->normalizePath('/' . $leaf);
        }

        $merged = rtrim($prefix, '/') . '/' . ltrim($leaf, '/');

        return $this->canonicalUrlResolver->normalizePath($merged);
    }

    /**
     * @return array<string, string> locale => normalized prefix path (for admin preview / routing-meta).
     */
    public function normalizedPrefixesMapForTargetClass(string $targetClass): array
    {
        if (! $this->enabled() || ! $this->tablesReady()) {
            return [];
        }

        $aliases = $this->targetModelAliases($targetClass);
        $out = [];

        $wildcard = ParentSegment::query()
            ->whereIn('target_model_class', $aliases)
            ->where('enabled', true)
            ->where('locale', '')
            ->orderBy('sort_order')
            ->first();

        if ($wildcard !== null) {
            $raw = trim((string) ($wildcard->normalized_prefix ?? ''));
            $out['*'] = $raw !== ''
                ? $this->canonicalUrlResolver->normalizePath('/' . ltrim($raw, '/'))
                : '/';
        }

        $orderedLocales = [];
        $seenLocale = [];

        foreach (getLocales() as $loc) {
            $loc = (string) $loc;
            if (isset($seenLocale[$loc])) {
                continue;
            }
            $seenLocale[$loc] = true;
            $orderedLocales[] = $loc;
        }

        foreach ($this->localesWithBindingsForAliases($aliases) as $loc) {
            $loc = (string) $loc;
            if (isset($seenLocale[$loc])) {
                continue;
            }
            $seenLocale[$loc] = true;
            $orderedLocales[] = $loc;
        }

        foreach ($orderedLocales as $loc) {
            $path = $this->normalizedPrefixForTargetLocale($targetClass, $loc);
            if ($path !== null) {
                $out[$loc] = $path;
            }
        }

        return $out;
    }

    /**
     * @param list<class-string|string> $aliases
     * @return list<string>
     */
    private function localesWithBindingsForAliases(array $aliases): array
    {
        if ($aliases === []) {
            return [];
        }

        return ParentSegment::query()
            ->whereIn('target_model_class', $aliases)
            ->where('locale', '!=', '')
            ->distinct()
            ->orderBy('locale')
            ->pluck('locale')
            ->map(fn ($loc) => (string) $loc)
            ->values()
            ->all();
    }

    /**
     * @return list<class-string|string>
     */
    private function targetModelAliases(string $targetClass): array
    {
        $aliases = [$targetClass];
        if (class_exists($targetClass) && is_a($targetClass, Model::class, true)) {
            try {
                /** @phpstan-ignore-next-line safe new for morph alias lookup */
                $aliases[] = (new $targetClass)->getMorphClass();
            } catch (\Throwable) {
            }
        }

        /** @var list<class-string|string> */
        return array_values(array_unique(array_filter(array_map('strval', $aliases))));
    }

    private function resolvePreferredEnabledBindingOrFallbackLocale(string $targetClass, string $locale): ?ParentSegment
    {
        $preferred = $this->fetchEnabledBindingForLocalePreference($targetClass, $locale);

        if ($preferred !== null) {
            return $preferred;
        }

        foreach ($this->fallbackLocaleCandidatesForSegments($targetClass) as $candidate) {
            if ($candidate === $locale) {
                continue;
            }
            $picked = $this->fetchEnabledBindingForLocalePreference($targetClass, $candidate);
            if ($picked !== null) {
                return $picked;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function fallbackLocaleCandidatesForSegments(string $targetClass): array
    {
        $candidates = [];
        $candidates[] = (string) modularousConfig('cms_routing.default_locale', config('app.locale'));

        $transFallback = config('translatable.fallback_locale');
        if (is_string($transFallback) && $transFallback !== '') {
            $candidates[] = $transFallback;
        }

        foreach (getLocales() as $loc) {
            $candidates[] = (string) $loc;
        }

        $aliases = $this->targetModelAliases($targetClass);
        foreach ($this->localesWithEnabledBindings($aliases) as $loc) {
            $candidates[] = $loc;
        }

        /** @var list<string> */
        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @param list<class-string|string> $aliases
     * @return list<string>
     */
    private function localesWithEnabledBindings(array $aliases): array
    {
        if ($aliases === []) {
            return [];
        }

        return ParentSegment::query()
            ->whereIn('target_model_class', $aliases)
            ->where('enabled', true)
            ->where('locale', '!=', '')
            ->distinct()
            ->orderBy('locale')
            ->pluck('locale')
            ->map(fn ($loc) => (string) $loc)
            ->values()
            ->all();
    }

    /**
     * Normalized binding prefix path, or {@code /} when the DB stores a deliberately blank prefix (locale-root URLs).
     */
    private function normalizedPathFromBinding(?ParentSegment $binding): ?string
    {
        if ($binding === null) {
            return null;
        }

        $raw = trim((string) ($binding->normalized_prefix ?? ''));

        if ($raw === '') {
            return '/';
        }

        return $this->canonicalUrlResolver->normalizePath('/' . ltrim($raw, '/'));
    }

    /**
     * One enabled binding: exact locale beats empty string (wildcard) for the same targets.
     */
    private function fetchEnabledBindingForLocalePreference(string $targetClass, string $preferredLocale): ?ParentSegment
    {
        $aliases = $this->targetModelAliases($targetClass);
        if ($aliases === []) {
            return null;
        }

        return ParentSegment::query()
            ->whereIn('target_model_class', $aliases)
            ->where('enabled', true)
            ->where(function ($q) use ($preferredLocale): void {
                $q->where('locale', $preferredLocale)->orWhere('locale', '');
            })
            ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [$preferredLocale])
            ->orderBy('sort_order')
            ->first();
    }
}
