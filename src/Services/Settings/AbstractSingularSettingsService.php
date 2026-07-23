<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Settings;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Repositories\Repository;

/**
 * Shared read/write helpers for IsSingular settings singletons (dot paths, locale maps, media leaves).
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
abstract class AbstractSingularSettingsService
{
    /** @var array<string, mixed>|null */
    private ?array $requestSnapshot = null;

    abstract protected function cacheKey(): string;

    abstract protected function cacheTtl(): int;

    /**
     * @return class-string<TModel>
     */
    abstract protected function modelClass(): string;

    abstract protected function repository(): Repository;

    /**
     * @return list<string>
     */
    abstract protected function settingsSections(): array;

    /**
     * Get a nested setting by dot path, resolving locale maps along the way.
     *
     * When the direct path is empty and the key has at least two segments, expands through
     * locale (+ optional first list index) using the last segment as an arbitrary leaf:
     * `site.logo.frontend` → `site.logo.{locale}.frontend`
     *                     → `site.logo.{locale}.0.frontend`
     *                     → `site.logo.{locale}.frontend.0`
     */
    public function get(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        $resolvedLocale = $locale ?? app()->getLocale();
        $snapshot = $this->snapshot();
        $segments = $key === '' ? [] : explode('.', $key);

        $value = $this->walkPath($snapshot, $key, $resolvedLocale);

        if (! $this->isEmptySettingValue($value) && ! $this->isExpandableListResult($value, $segments)) {
            return $value;
        }

        if (count($segments) >= 2) {
            $leaf = (string) $segments[array_key_last($segments)];
            $parentKey = implode('.', array_slice($segments, 0, -1));
            $expanded = $this->resolveLeafViaLocalePaths($snapshot, $parentKey, $leaf, $resolvedLocale);

            if (! $this->isEmptySettingValue($expanded)) {
                return $expanded;
            }
        }

        if (! $this->isEmptySettingValue($value)) {
            return $value;
        }

        return $default;
    }

    /**
     * Alias of {@see get()}.
     */
    public function value(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        return $this->get($key, $default, $locale);
    }

    /**
     * Get a setting; when the resolved value is a list, return its first element.
     */
    public function first(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        $resolved = $this->get($key, null, $locale);

        if ($resolved === null) {
            return $default;
        }

        if (is_array($resolved) && Arr::isList($resolved)) {
            return $resolved[0] ?? $default;
        }

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(?string $locale = null): array
    {
        $snapshot = $this->snapshot();
        $resolvedLocale = $locale ?? app()->getLocale();

        return $this->resolveTranslatedTree($snapshot, $resolvedLocale);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->snapshot(), $key);
    }

    /**
     * True when the resolved value for {@see $key} is non-empty (after locale/leaf expansion).
     */
    public function filled(string $key, ?string $locale = null): bool
    {
        return ! $this->isEmptySettingValue($this->get($key, null, $locale));
    }

    public function set(string $key, mixed $value): void
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::single();
        $fields = $this->snapshotFromModel($model);
        Arr::set($fields, $key, $value);

        $this->repository()->update($model->id, $fields);
        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        $this->requestSnapshot = null;
        Cache::forget($this->cacheKey());
        ModularousCache::forget($this->cacheKey());
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        if ($this->requestSnapshot !== null) {
            return $this->requestSnapshot;
        }

        $ttl = $this->cacheTtl();

        $this->requestSnapshot = Cache::remember($this->cacheKey(), $ttl, function (): array {
            return $this->buildSnapshotFromDatabase();
        });

        return $this->requestSnapshot;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSnapshotFromDatabase(): array
    {
        $modelClass = $this->modelClass();

        if (! class_exists($modelClass)) {
            return $this->defaultSnapshot();
        }

        try {
            $model = $modelClass::single();
        } catch (\Throwable) {
            return $this->defaultSnapshot();
        }

        return $this->snapshotFromModel($model);
    }

    /**
     * @param TModel $model
     * @return array<string, mixed>
     */
    protected function snapshotFromModel(object $model): array
    {
        $snapshot = $this->defaultSnapshot();

        foreach ($this->settingsSections() as $section) {
            $value = $model->getAttribute($section);
            if ($value !== null) {
                $snapshot[$section] = is_array($value) ? $value : [];
            }
        }

        if (isset($snapshot['smtp']['password']) && is_string($snapshot['smtp']['password']) && $snapshot['smtp']['password'] !== '') {
            $snapshot['smtp']['password'] = $this->decryptSecret($snapshot['smtp']['password']);
        }

        if (isset($snapshot['down_presets']['secret']) && is_string($snapshot['down_presets']['secret']) && $snapshot['down_presets']['secret'] !== '') {
            $snapshot['down_presets']['secret'] = $this->decryptSecret($snapshot['down_presets']['secret']);
        }

        return $snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultSnapshot(): array
    {
        $snapshot = [];

        foreach ($this->settingsSections() as $section) {
            $snapshot[$section] = [];
        }

        return $snapshot;
    }

    protected function decryptSecret(string $value): string
    {
        try {
            return decrypt($value, false);
        } catch (DecryptException) {
            return $value;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function walkPath(array $data, string $key, string $locale): mixed
    {
        if ($key === '') {
            return $this->resolveTranslatedValue($data, $locale);
        }

        $cursor = $data;

        foreach (explode('.', $key) as $segment) {
            if (is_array($cursor) && $this->looksLikeLocaleMap($cursor)) {
                $cursor = $this->resolveTranslatedValue($cursor, $locale);
            }

            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return null;
            }

            $cursor = $cursor[$segment];
        }

        return $this->resolveTranslatedValue($cursor, $locale);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function resolveLeafViaLocalePaths(array $data, string $parentKey, string $leaf, string $locale): mixed
    {
        $parent = $parentKey === ''
            ? $data
            : $this->walkPathRaw($data, $parentKey);

        if (! is_array($parent)) {
            return null;
        }

        $fallback = (string) modularousConfig('fallback_locale', config('app.fallback_locale', 'en'));
        $locales = [];

        foreach ([$locale, $fallback, '*'] as $candidate) {
            if ($candidate === '' || in_array($candidate, $locales, true)) {
                continue;
            }

            $locales[] = $candidate;
        }

        foreach ($locales as $tryLocale) {
            if (! array_key_exists($tryLocale, $parent)) {
                continue;
            }

            $listDeferred = null;

            foreach ([
                "{$tryLocale}.{$leaf}",
                "{$tryLocale}.0.{$leaf}",
                "{$tryLocale}.{$leaf}.0",
            ] as $relativePath) {
                $candidate = data_get($parent, $relativePath);

                if ($this->isEmptySettingValue($candidate)) {
                    continue;
                }

                if (is_array($candidate) && Arr::isList($candidate)) {
                    $listDeferred ??= $candidate;

                    continue;
                }

                return $candidate;
            }

            if ($listDeferred !== null) {
                return $listDeferred;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function walkPathRaw(array $data, string $key): mixed
    {
        if ($key === '') {
            return $data;
        }

        $cursor = $data;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return null;
            }

            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    /**
     * @param list<string> $segments
     */
    protected function isExpandableListResult(mixed $value, array $segments): bool
    {
        return count($segments) >= 2
            && is_array($value)
            && Arr::isList($value);
    }

    protected function isEmptySettingValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    protected function resolveTranslatedValue(mixed $value, string $locale): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if ($this->looksLikeLocaleMap($value)) {
            $fallback = (string) modularousConfig('fallback_locale', config('app.fallback_locale', 'en'));

            return $value[$locale]
                ?? $value[$fallback]
                ?? $value['*']
                ?? reset($value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $tree
     * @return array<string, mixed>
     */
    protected function resolveTranslatedTree(array $tree, string $locale): array
    {
        $resolved = [];

        foreach ($tree as $key => $value) {
            if (is_array($value) && ! $this->looksLikeLocaleMap($value) && Arr::isAssoc($value)) {
                $resolved[$key] = $this->resolveTranslatedTree($value, $locale);

                continue;
            }

            $resolved[$key] = $this->resolveTranslatedValue($value, $locale);
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $value
     */
    protected function looksLikeLocaleMap(array $value): bool
    {
        if ($value === [] || Arr::isList($value)) {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (! is_string($key) || ! $this->looksLikeLocaleKey($key)) {
                return false;
            }
        }

        return true;
    }

    protected function looksLikeLocaleKey(string $key): bool
    {
        if ($key === '*') {
            return true;
        }

        return (bool) preg_match('/^[a-z]{2}([_-][A-Za-z]{2,8})?$/', $key);
    }
}
