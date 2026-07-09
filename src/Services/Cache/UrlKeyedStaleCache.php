<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Filesystem-backed public HTML keyed by locale + normalized URL path (+ optional query suffix).
 *
 * Layout: {base}/{locale}/{sha256(cache_lookup_key)}.html + .meta
 *
 * cache_lookup_key is {@see composeLookupKey()}: normalized path, or path + '?' + sorted query.
 */
final class UrlKeyedStaleCache
{
    public const FRESHNESS_HIT = 'HIT';

    public const FRESHNESS_STALE = 'STALE';

    public function __construct(
        private readonly string $basePath,
        private readonly int $defaultStaleTtl = 604800,
    ) {}

    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function put(
        string $locale,
        string $cacheLookupKey,
        string $html,
        array $meta,
        ?int $freshTtl = null,
        ?int $staleTtl = null,
    ): bool {
        $locale = $this->normalizeLocale($locale);
        $cacheLookupKey = $this->normalizeLookupKey($cacheLookupKey);
        [$normalizedPath, $normalizedQuery] = $this->splitLookupKey($cacheLookupKey);
        $path = $this->htmlPath($locale, $cacheLookupKey);
        if ($path === null) {
            return false;
        }

        $this->ensureDirectory(dirname($path));

        if (file_put_contents($path, $html, LOCK_EX) === false) {
            return false;
        }

        $now = time();
        $freshTtl ??= (int) ($meta['fresh_ttl'] ?? 900);
        $staleTtl ??= $this->defaultStaleTtl;

        $meta['locale'] = $locale;
        $meta['normalized_path'] = $normalizedPath;
        $meta['normalized_query'] = $normalizedQuery !== '' ? $normalizedQuery : null;
        $meta['cache_lookup_key'] = $cacheLookupKey;
        $meta['warmed_at'] = $meta['warmed_at'] ?? gmdate('c', $now);
        $meta['expires_at'] = $now + max(1, $freshTtl);
        $meta['stale_expires_at'] = $now + max(1, $staleTtl);

        $metaPath = $this->metaPath($path);
        file_put_contents($metaPath, (string) json_encode($meta), LOCK_EX);

        return true;
    }

    /**
     * @return array{html: string, meta: array<string, mixed>, freshness: string}|null
     */
    public function get(string $locale, string $cacheLookupKey): ?array
    {
        $locale = $this->normalizeLocale($locale);
        $cacheLookupKey = $this->normalizeLookupKey($cacheLookupKey);
        $path = $this->htmlPath($locale, $cacheLookupKey);
        if ($path === null || ! is_file($path)) {
            return null;
        }

        $meta = $this->readMeta($path);
        if ($meta === null) {
            return null;
        }

        $now = time();
        $staleExpiresAt = (int) ($meta['stale_expires_at'] ?? 0);
        if ($staleExpiresAt > 0 && $now > $staleExpiresAt) {
            $this->deleteFilePair($path);

            return null;
        }

        $html = file_get_contents($path);
        if (! is_string($html) || $html === '') {
            return null;
        }

        $expiresAt = (int) ($meta['expires_at'] ?? 0);
        $freshness = ($expiresAt > 0 && $now <= $expiresAt)
            ? self::FRESHNESS_HIT
            : self::FRESHNESS_STALE;

        return [
            'html' => $html,
            'meta' => $meta,
            'freshness' => $freshness,
        ];
    }

    public function forget(string $locale, string $cacheLookupKey): bool
    {
        $path = $this->htmlPath(
            $this->normalizeLocale($locale),
            $this->normalizeLookupKey($cacheLookupKey),
        );
        if ($path === null) {
            return false;
        }

        return $this->deleteFilePair($path);
    }

    /**
     * Deletes all cached variants for a locale + normalized path (any query suffix).
     */
    public function forgetPathVariants(string $locale, string $normalizedPath): int
    {
        $normalizedPath = $this->normalizePath($normalizedPath);

        return $this->forgetMatchingMeta(
            fn (array $meta): bool => isset($meta['normalized_path'])
                && $this->normalizePath((string) $meta['normalized_path']) === $normalizedPath,
            $this->normalizeLocale($locale),
        );
    }

    /**
     * Deletes all URL stale entries tied to a model (all locales, paths, and query variants).
     *
     * @param class-string<Model>|string $modelClass
     */
    public function forgetByRelation(string $modelClass, int|string $id): int
    {
        $types = $this->urlableTypesForModelClass($modelClass);
        $id = (string) $id;

        return $this->forgetMatchingMeta(
            fn (array $meta): bool => isset($meta['urlable_id'], $meta['urlable_type'])
                && (string) $meta['urlable_id'] === $id
                && in_array((string) $meta['urlable_type'], $types, true),
        );
    }

    /**
     * Deletes all URL stale entries for a module route (all records, paths, and query variants).
     */
    public function forgetByModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        $moduleName = $this->normalizeModuleSegment($moduleName);
        $moduleRouteName = $this->normalizeModuleSegment($moduleRouteName);

        return $this->forgetMatchingMeta(
            fn (array $meta): bool => isset($meta['module'], $meta['route'])
                && $this->normalizeModuleSegment((string) $meta['module']) === $moduleName
                && $this->normalizeModuleSegment((string) $meta['route']) === $moduleRouteName,
        );
    }

    public function composeLookupKey(string $normalizedPath, string $querySuffix = ''): string
    {
        $path = $this->normalizePath($normalizedPath);
        $querySuffix = trim($querySuffix);

        if ($querySuffix === '') {
            return $path;
        }

        return $path . '?' . ltrim($querySuffix, '?');
    }

    public function pathHash(string $cacheLookupKey): string
    {
        return hash('sha256', $this->normalizeLookupKey($cacheLookupKey));
    }

    public function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function splitLookupKey(string $cacheLookupKey): array
    {
        $pos = strpos($cacheLookupKey, '?');

        if ($pos === false) {
            return [$this->normalizePath($cacheLookupKey), ''];
        }

        return [
            $this->normalizePath(substr($cacheLookupKey, 0, $pos)),
            substr($cacheLookupKey, $pos + 1),
        ];
    }

    protected function normalizeLookupKey(string $cacheLookupKey): string
    {
        [$path, $query] = $this->splitLookupKey($cacheLookupKey);

        if ($query === '') {
            return $path;
        }

        return $path . '?' . $query;
    }

    protected function normalizeLocale(string $locale): string
    {
        if (class_exists(\Modules\Cms\Support\CmsPublicPresentationItemCache::class)) {
            return \Modules\Cms\Support\CmsPublicPresentationItemCache::normalizeCacheLocale($locale);
        }

        $locale = trim(mb_strtolower($locale));

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

    protected function htmlPath(string $locale, string $cacheLookupKey): ?string
    {
        $cacheLookupKey = $this->normalizeLookupKey($cacheLookupKey);
        if ($locale === '' || $cacheLookupKey === '') {
            return null;
        }

        return $this->basePath
            . '/' . $locale
            . '/' . $this->pathHash($cacheLookupKey) . '.html';
    }

    protected function metaPath(string $htmlPath): string
    {
        return $htmlPath . '.meta';
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function readMeta(string $htmlPath): ?array
    {
        $metaPath = $this->metaPath($htmlPath);
        if (! is_file($metaPath)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($metaPath), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function deleteFilePair(string $htmlPath): bool
    {
        $metaPath = $this->metaPath($htmlPath);
        if (is_file($metaPath)) {
            @unlink($metaPath);
        }

        if (! is_file($htmlPath)) {
            return false;
        }

        return @unlink($htmlPath);
    }

    /**
     * @param callable(array<string, mixed>): bool $matcher
     */
    protected function forgetMatchingMeta(callable $matcher, ?string $locale = null): int
    {
        $deleted = 0;

        foreach ($this->localeDirectories($locale) as $directory) {
            foreach (scandir($directory) ?: [] as $item) {
                if ($item === '.' || $item === '..' || ! str_ends_with($item, '.meta')) {
                    continue;
                }

                $metaPath = $directory . '/' . $item;
                $decoded = json_decode((string) file_get_contents($metaPath), true);
                if (! is_array($decoded) || ! $matcher($decoded)) {
                    continue;
                }

                $htmlPath = substr($metaPath, 0, -5);
                if ($this->deleteFilePair($htmlPath)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * @return list<string>
     */
    protected function localeDirectories(?string $locale = null): array
    {
        if ($locale !== null) {
            $directory = $this->basePath . '/' . $this->normalizeLocale($locale);

            return is_dir($directory) ? [$directory] : [];
        }

        if (! is_dir($this->basePath)) {
            return [];
        }

        $directories = [];
        foreach (scandir($this->basePath) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $this->basePath . '/' . $item;
            if (is_dir($path)) {
                $directories[] = $path;
            }
        }

        return $directories;
    }

    /**
     * @param class-string<Model>|string $modelClass
     * @return list<string>
     */
    protected function urlableTypesForModelClass(string $modelClass): array
    {
        $types = [$modelClass];

        try {
            if (class_exists($modelClass)) {
                $model = new $modelClass;
                if ($model instanceof Model && method_exists($model, 'getMorphClass')) {
                    $types[] = $model->getMorphClass();
                }
            }
        } catch (\Throwable) {
            // Model may be abstract or missing dependencies during cache-only operations.
        }

        return array_values(array_unique($types));
    }

    protected function normalizeModuleSegment(string $segment): string
    {
        return Str::studly($segment);
    }
}
