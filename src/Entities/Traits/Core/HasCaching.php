<?php

namespace Unusualify\Modularous\Entities\Traits\Core;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\UrlRoute;
use Unusualify\Modularous\Entities\Observers\CacheObserver;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Traits\Cache\Cacheable;

/**
 * Trait HasCaching
 *
 * Add this trait to models that should have automatic cache invalidation.
 * The cache will be invalidated when the model is created, updated, deleted,
 * restored, or force deleted.
 */
trait HasCaching
{
    use Cacheable;

    /**
     * Boot the HasCaching trait.
     */
    public static function bootHasCaching(): void
    {
        static::observe(CacheObserver::class);
    }

    /**
     * Determine if cache invalidation should be performed for this model.
     * Override this method to conditionally disable cache invalidation.
     */
    public function shouldCacheInvalidate(): bool
    {
        return true;
    }

    /**
     * Temporarily disable cache invalidation for the current instance.
     */
    public function withoutCacheInvalidation(): static
    {
        $this->skipCacheInvalidation = true;

        return $this;
    }

    /**
     * Re-enable cache invalidation for the current instance.
     */
    public function withCacheInvalidation(): static
    {
        $this->skipCacheInvalidation = false;

        return $this;
    }

    protected function newInstanceHasCaching($instance, $attributes, $exists): static
    {
        $instance->dependentWarmingEnabled = $this->dependentWarmingEnabled ?? true;

        return $instance;
    }

    /**
     * Admin table/form chips for public presentationItem cache status (one chip per locale).
     */
    protected function presentationItemCacheFormatted(): Attribute
    {
        return new Attribute(
            get: function () {
                $statuses = $this->resolvePresentationItemCacheStatuses();

                return $this->formatPresentationItemCacheChips($statuses);
            },
        );
    }

    /**
     * @return list<array{locale: string, freshness: string, expires_at: int|null, stale_expires_at: int|null}>
     */
    protected function resolvePresentationItemCacheStatuses(): array
    {
        if ($this->getKey() === null || ! ModularousCache::isPresentationCacheEnabled()) {
            return $this->presentationItemMissStatusesForLocales($this->resolvePresentationItemLocales());
        }

        $store = ModularousCache::getPresentationCacheStore();

        if ($store === 'url') {
            return $this->inspectUrlPresentationItemCachesByLocale();
        }

        if ($store === 'model') {
            return $this->inspectModelPresentationItemCachesByLocale();
        }

        return $this->presentationItemMissStatusesForLocales($this->resolvePresentationItemLocales());
    }

    /**
     * @return list<array{locale: string, freshness: string, expires_at: int|null, stale_expires_at: int|null}>
     */
    protected function inspectUrlPresentationItemCachesByLocale(): array
    {
        $pathsByLocale = $this->resolvePresentationItemLocalePaths();
        $locales = $this->resolvePresentationItemLocales(array_keys($pathsByLocale));
        $urlStore = ModularousCache::getUrlPresentationCacheStore();
        $statuses = [];

        foreach ($locales as $locale) {
            $path = $pathsByLocale[$locale] ?? null;
            if (! is_string($path) || $path === '') {
                $statuses[] = $this->presentationItemMissStatus($locale);

                continue;
            }

            $entry = $urlStore->get($locale, $path);
            if ($entry === null) {
                $statuses[] = $this->presentationItemMissStatus($locale);

                continue;
            }

            $meta = is_array($entry['meta'] ?? null) ? $entry['meta'] : [];
            $expiresAt = (int) ($meta['expires_at'] ?? 0);
            $staleExpiresAt = (int) ($meta['stale_expires_at'] ?? 0);

            // Admin UI validity = retention window (STALE_TTL), not store fresh HIT/STALE split.
            $statuses[] = $this->presentationItemStatusFromRetention(
                $locale,
                $expiresAt > 0 ? $expiresAt : null,
                $staleExpiresAt > 0 ? $staleExpiresAt : null,
            );
        }

        return $statuses;
    }

    /**
     * @return list<array{locale: string, freshness: string, expires_at: int|null, stale_expires_at: int|null}>
     */
    protected function inspectModelPresentationItemCachesByLocale(): array
    {
        $moduleName = $this->getCacheModuleName();
        $routeName = $this->getCacheModuleRouteName();

        if (! is_string($moduleName) || $moduleName === '' || ! is_string($routeName) || $routeName === '') {
            return $this->presentationItemMissStatusesForLocales($this->resolvePresentationItemLocales());
        }

        $byLocale = ModularousCache::getStaleFileCache()->inspectLocalesByModuleRouteId(
            $moduleName,
            $routeName,
            $this->getKey(),
        );

        $discoveredLocales = array_values(array_filter(
            array_keys($byLocale),
            fn (string $locale) => $locale !== '_',
        ));
        $locales = $this->resolvePresentationItemLocales($discoveredLocales);
        $statuses = [];

        $freshTtl = ModularousCache::getTtl('presentationItem', $moduleName, $routeName);
        $staleTtl = ModularousCache::getStaleTtl('presentationItem');

        foreach ($locales as $locale) {
            $inspected = $byLocale[$locale] ?? null;
            if ($inspected === null) {
                $statuses[] = $this->presentationItemMissStatus($locale);

                continue;
            }

            $expiresAt = isset($inspected['expires_at']) && (int) $inspected['expires_at'] > 0
                ? (int) $inspected['expires_at']
                : null;
            $staleExpiresAt = null;
            if ($expiresAt !== null && $staleTtl > 0) {
                $warmedAt = $expiresAt - max(1, $freshTtl);
                $staleExpiresAt = $warmedAt + max(1, $staleTtl);
            }

            $statuses[] = $this->presentationItemStatusFromRetention($locale, $expiresAt, $staleExpiresAt);
        }

        return $statuses;
    }

    /**
     * Map cache retention to admin chip state.
     *
     * Valid (HIT) while {@see $staleExpiresAt} (MODULAROUS_PRESENTATION_CACHE_STALE_TTL) is in the future.
     * Fresh {@see $expiresAt} is ignored for label/color — only used as fallback when stale end is missing.
     *
     * @return array{locale: string, freshness: string, expires_at: int|null, stale_expires_at: int|null}
     */
    protected function presentationItemStatusFromRetention(
        string $locale,
        ?int $expiresAt,
        ?int $staleExpiresAt,
    ): array {
        $now = time();
        $validityEnd = is_int($staleExpiresAt) && $staleExpiresAt > 0
            ? $staleExpiresAt
            : $expiresAt;

        if ($validityEnd === null || $validityEnd <= 0 || $now > $validityEnd) {
            return $this->presentationItemMissStatus($locale);
        }

        return [
            'locale' => $locale,
            'freshness' => 'HIT',
            'expires_at' => $expiresAt,
            'stale_expires_at' => $staleExpiresAt,
        ];
    }

    /**
     * @param list<string> $extraLocales
     * @return list<string>
     */
    protected function resolvePresentationItemLocales(array $extraLocales = []): array
    {
        $locales = [];

        foreach (array_merge($this->configuredPresentationItemLocales(), $extraLocales, array_keys($this->resolvePresentationItemLocalePaths())) as $locale) {
            $normalized = $this->normalizePresentationItemLocale((string) $locale);
            if ($normalized === '' || $normalized === '_') {
                continue;
            }
            $locales[$normalized] = $normalized;
        }

        if ($locales === []) {
            $fallback = $this->normalizePresentationItemLocale(app()->getLocale());
            $locales[$fallback] = $fallback;
        }

        ksort($locales);

        return array_values($locales);
    }

    /**
     * @return list<string>
     */
    protected function configuredPresentationItemLocales(): array
    {
        $raw = config('app.locales', config('translatable.locales', [config('app.locale', 'en')]));
        if (! is_array($raw)) {
            return [(string) $raw];
        }

        $locales = [];
        foreach ($raw as $key => $value) {
            if (is_string($key) && ! is_numeric($key) && is_array($value)) {
                $locales[] = $key;

                continue;
            }
            if (is_string($value) || is_int($value)) {
                $locales[] = (string) $value;
            }
        }

        return $locales;
    }

    /**
     * @param list<string> $locales
     * @return list<array{locale: string, freshness: string, expires_at: int|null, stale_expires_at: int|null}>
     */
    protected function presentationItemMissStatusesForLocales(array $locales): array
    {
        return array_map(fn (string $locale) => $this->presentationItemMissStatus($locale), $locales);
    }

    /**
     * @return array{locale: string, freshness: string, expires_at: int|null, stale_expires_at: int|null}
     */
    protected function presentationItemMissStatus(string $locale): array
    {
        return [
            'locale' => $locale,
            'freshness' => 'MISS',
            'expires_at' => null,
            'stale_expires_at' => null,
        ];
    }

    /**
     * @return array<string, string> locale => normalized path
     */
    protected function resolvePresentationItemLocalePaths(): array
    {
        if (! $this instanceof Model || $this->getKey() === null) {
            return [];
        }

        if (! class_exists(UrlRoute::class)) {
            return [];
        }

        try {
            $table = (new UrlRoute)->getTable();
            if (! Schema::hasTable($table)) {
                return [];
            }

            $rows = UrlRoute::query()
                ->where('urlable_type', $this->getMorphClass())
                ->where('urlable_id', $this->getKey())
                ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
                ->get(['locale', 'normalized_path']);
        } catch (\Throwable) {
            return [];
        }

        $paths = [];
        foreach ($rows as $row) {
            $locale = $this->normalizePresentationItemLocale((string) $row->locale);
            if ($locale === '') {
                continue;
            }
            $paths[$locale] = (string) $row->normalized_path;
        }

        return $paths;
    }

    protected function normalizePresentationItemLocale(?string $locale): string
    {
        $locale = trim(mb_strtolower((string) ($locale ?? '')));

        if ($locale === '') {
            return trim(mb_strtolower((string) config('app.locale', 'en')));
        }

        if (str_contains($locale, '_')) {
            return explode('_', $locale)[0];
        }

        if (str_contains($locale, '-')) {
            return explode('-', $locale)[0];
        }

        return $locale;
    }

    /**
     * Chip "until" uses hard retention {@see stale_expires_at}
     * (= warmed_at + MODULAROUS_PRESENTATION_CACHE_STALE_TTL), not fresh {@see expires_at}.
     *
     * @param list<array{locale: string, freshness: string, expires_at: int|null, stale_expires_at?: int|null}> $statuses
     */
    protected function formatPresentationItemCacheChips(array $statuses): string
    {
        if ($statuses === []) {
            $statuses = [[
                'locale' => $this->normalizePresentationItemLocale(app()->getLocale()),
                'freshness' => 'MISS',
                'expires_at' => null,
                'stale_expires_at' => null,
            ]];
        }

        $chips = implode('', array_map(
            fn (array $status) => $this->formatPresentationItemCacheChip($status),
            $statuses,
        ));

        return "<v-chip-group column>{$chips}</v-chip-group>";
    }

    /**
     * @param array{locale?: string, freshness: string, expires_at: int|null, stale_expires_at?: int|null} $status
     */
    protected function formatPresentationItemCacheChip(array $status): string
    {
        $locale = mb_strtoupper((string) ($status['locale'] ?? ''));
        $freshness = $status['freshness'] ?? 'MISS';
        $expiresAt = $status['expires_at'] ?? null;
        $staleExpiresAt = $status['stale_expires_at'] ?? null;
        $validityEnd = is_int($staleExpiresAt) && $staleExpiresAt > 0
            ? $staleExpiresAt
            : $expiresAt;
        $expiresLabel = $this->formatPresentationItemExpiryLabel($validityEnd);
        $chipLabel = $locale !== '' ? $locale : '?';

        if ($freshness === 'HIT') {
            $tooltip = $expiresLabel !== null
                ? __('messages.resource-cache.presentation-item-column.fresh', [
                    'expires' => $expiresLabel,
                ])
                : __('messages.resource-cache.presentation-item-column.fresh-no-expiry');
            $color = 'success';
            $icon = 'mdi-check-circle';
        } elseif ($freshness === 'STALE') {
            $tooltip = $expiresLabel !== null
                ? __('messages.resource-cache.presentation-item-column.stale', [
                    'expires' => $expiresLabel,
                ])
                : __('messages.resource-cache.presentation-item-column.stale-no-expiry');
            $color = 'primary';
            $icon = 'mdi-clock-alert-outline';
        } else {
            $tooltip = __('messages.resource-cache.presentation-item-column.missing');
            $color = 'secondary';
            $icon = 'mdi-close-circle-outline';
        }

        $tooltipAttr = e($tooltip);

        return "<v-tooltip text=\"{$tooltipAttr}\" location=\"top\"><v-chip color=\"{$color}\" prepend-icon=\"{$icon}\" variant=\"text\">{$chipLabel}</v-chip></v-tooltip>";
    }

    protected function formatPresentationItemExpiryLabel(?int $timestamp): ?string
    {
        if (! is_int($timestamp) || $timestamp <= 0) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp)
            ->timezone(config('app.timezone', 'UTC'))
            ->format('Y-m-d H:i');
    }
}
