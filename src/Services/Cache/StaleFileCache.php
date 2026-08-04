<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Cache;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Cms\Support\CmsPublicPresentationItemCache;

/**
 * Filesystem-backed stale HTML store for SWR (independent of Redis).
 *
 * Path layout: {base}/{Module}/{Route}/{Model}/{id}/{locale}/{paramsHash}.html
 * paramsHash encodes variant (e.g. full vs wrapped); locale is a dedicated segment.
 *
 * {@see LOCALE_RELATION_KEY} may be passed in $relations for explicit locale on get/put.
 */
final class StaleFileCache
{
    public const LOCALE_RELATION_KEY = '__stale_locale__';

    public function __construct(
        private readonly string $basePath,
        private readonly int $defaultTtl = 86400,
    ) {}

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function put(string $cacheKey, string $html, ?int $ttl = null, array $relations = []): bool
    {
        $relations = $this->relationsWithResolvedLocale($cacheKey, $relations);
        $path = $this->resolvePath($cacheKey, $relations);
        if ($path === null) {
            return false;
        }

        $this->ensureDirectory(dirname($path));

        $written = file_put_contents($path, $html, LOCK_EX);
        if ($written === false) {
            return false;
        }

        $metaPath = $this->metaPath($path);
        $expiresAt = time() + ($ttl ?? $this->defaultTtl);
        file_put_contents($metaPath, (string) json_encode(['expires_at' => $expiresAt]), LOCK_EX);

        return true;
    }

    /**
     * @param array<string, int|string|array<int|string>> $relations
     */
    public function get(string $cacheKey, mixed $default = null, array $relations = []): mixed
    {
        $relations = $this->relationsWithResolvedLocale($cacheKey, $relations);
        $paths = [];

        if ($relations !== []) {
            $primary = $this->resolvePath($cacheKey, $relations);
            if ($primary !== null) {
                $paths[] = $primary;
            }
        }

        foreach ($this->resolveReadPaths($cacheKey, $this->localeFromRelations($relations)) as $path) {
            if (! in_array($path, $paths, true)) {
                $paths[] = $path;
            }
        }

        foreach ($paths as $path) {
            $contents = $this->readPathIfFresh($path);
            if (is_string($contents) && $contents !== '') {
                return $contents;
            }
        }

        return $default;
    }

    public function forget(string $cacheKey): bool
    {
        $forgot = false;
        foreach ($this->resolveReadPaths($cacheKey) as $path) {
            if (is_file($path) && $this->deleteFilePair($path)) {
                $forgot = true;
            }
        }

        return $forgot;
    }

    /**
     * Delete all stale files for a module route + record id (explicit per-id purge).
     */
    public function forgetByModuleRouteId(string $moduleName, string $moduleRouteName, int|string $id): int
    {
        $module = Str::studly($moduleName);
        $route = Str::studly($moduleRouteName);
        $id = (string) $id;

        $deleted = 0;
        $patterns = [
            $this->basePath . '/' . $module . '/' . $route . '/' . $id . '/*',
            $this->basePath . '/' . $module . '/' . $route . '/' . $id . '/*/*',
            $this->basePath . '/' . $module . '/' . $route . '/*/' . $id . '/*',
            $this->basePath . '/' . $module . '/' . $route . '/*/' . $id . '/*/*',
        ];

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                if (is_file($file) && str_ends_with($file, '.html')) {
                    if ($this->deleteFilePair($file)) {
                        $deleted++;
                    }
                }
            }
        }

        return $deleted;
    }

    /**
     * Delete all stale files for a module route (all record ids and locales).
     */
    public function forgetByModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        $module = Str::studly($moduleName);
        $route = Str::studly($moduleRouteName);
        $directory = $this->basePath . '/' . $module . '/' . $route;

        if (! is_dir($directory)) {
            return 0;
        }

        $deleted = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getPathname(), '.html')) {
                continue;
            }

            if ($this->deleteFilePair($file->getPathname())) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Delete stale files tied to a related model (relation-tag invalidation).
     */
    public function forgetByRelation(string $modelClass, int|string $id): int
    {
        $basename = class_basename($modelClass);
        $patterns = [
            $this->basePath . '/*/*/' . $basename . '/' . $id . '/*',
            $this->basePath . '/*/*/' . $basename . '/' . $id . '/*/*',
        ];

        $deleted = 0;
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                if (is_file($file) && str_ends_with($file, '.html')) {
                    if ($this->deleteFilePair($file)) {
                        $deleted++;
                    }
                } elseif (is_file($file) && str_ends_with($file, '.meta')) {
                    @unlink($file);
                }
            }
        }

        return $deleted;
    }

    /**
     * Non-destructive status peek for presentationItem files under a module/route/id.
     *
     * Does not delete expired files (unlike {@see get()} / {@see readPathIfFresh()}).
     *
     * @return array{freshness: string, expires_at: int, meta: array<string, mixed>}|null
     */
    public function inspectByModuleRouteId(string $moduleName, string $moduleRouteName, int|string $id): ?array
    {
        $byLocale = $this->inspectLocalesByModuleRouteId($moduleName, $moduleRouteName, $id);
        if ($byLocale === []) {
            return null;
        }

        $best = null;
        foreach ($byLocale as $candidate) {
            if ($best === null) {
                $best = $candidate;

                continue;
            }

            if ($best['freshness'] !== 'HIT' && $candidate['freshness'] === 'HIT') {
                $best = $candidate;

                continue;
            }

            if ($best['freshness'] === $candidate['freshness'] && $candidate['expires_at'] > $best['expires_at']) {
                $best = $candidate;
            }
        }

        return $best;
    }

    /**
     * Non-destructive per-locale status peek for presentationItem files under a module/route/id.
     *
     * @return array<string, array{freshness: string, expires_at: int, meta: array<string, mixed>, locale: string}>
     */
    public function inspectLocalesByModuleRouteId(string $moduleName, string $moduleRouteName, int|string $id): array
    {
        $module = Str::studly($moduleName);
        $route = Str::studly($moduleRouteName);
        $id = (string) $id;
        $now = time();
        $byLocale = [];

        $patterns = [
            $this->basePath . '/' . $module . '/' . $route . '/' . $id . '/*.meta',
            $this->basePath . '/' . $module . '/' . $route . '/' . $id . '/*/*.meta',
            $this->basePath . '/' . $module . '/' . $route . '/*/' . $id . '/*.meta',
            $this->basePath . '/' . $module . '/' . $route . '/*/' . $id . '/*/*.meta',
        ];

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $metaPath) {
                if (! is_file($metaPath)) {
                    continue;
                }

                $decoded = json_decode((string) file_get_contents($metaPath), true);
                if (! is_array($decoded)) {
                    continue;
                }

                $locale = $this->localeFromMetaPath($metaPath, $decoded);
                if ($locale === '') {
                    $locale = '_';
                }

                $expiresAt = (int) ($decoded['expires_at'] ?? 0);
                $freshness = ($expiresAt > 0 && $now <= $expiresAt)
                    ? 'HIT'
                    : 'STALE';

                $candidate = [
                    'freshness' => $freshness,
                    'expires_at' => $expiresAt,
                    'meta' => $decoded,
                    'locale' => $locale,
                ];

                $existing = $byLocale[$locale] ?? null;
                if ($existing === null) {
                    $byLocale[$locale] = $candidate;

                    continue;
                }

                if ($existing['freshness'] !== 'HIT' && $freshness === 'HIT') {
                    $byLocale[$locale] = $candidate;

                    continue;
                }

                if ($existing['freshness'] === $freshness && $expiresAt > $existing['expires_at']) {
                    $byLocale[$locale] = $candidate;
                }
            }
        }

        return $byLocale;
    }

    /**
     * @param array<string, mixed> $meta
     */
    protected function localeFromMetaPath(string $metaPath, array $meta): string
    {
        $fromMeta = isset($meta['locale']) ? $this->normalizeCacheLocale((string) $meta['locale']) : '';
        if ($fromMeta !== '') {
            return $fromMeta;
        }

        // …/{id}/{locale}/{hash}.html.meta or …/{id}/{hash}.html.meta
        $parts = explode('/', str_replace('\\', '/', $metaPath));
        $file = $parts[array_key_last($parts)] ?? '';
        if (! str_ends_with($file, '.html.meta')) {
            return '';
        }

        $parent = $parts[count($parts) - 2] ?? '';
        if ($parent === '' || preg_match('/^[a-f0-9]{32}$/', $parent) === 1) {
            return '';
        }

        // Parent looks like a locale segment (en, tr, …), not the record id.
        if (preg_match('/^[a-z]{2}([_-][a-zA-Z]+)?$/', $parent) === 1) {
            return $this->normalizeCacheLocale($parent);
        }

        return '';
    }

    public function resolvePath(string $cacheKey, array $relations = []): ?string
    {
        if (preg_match(
            '#^[^:]+:([^:]+):([^:]+):presentationItem:([^:]+):([a-f0-9]+)$#',
            $cacheKey,
            $matches,
        )) {
            [, $module, $route, $id, $paramsHash] = $matches;
            $modelSegment = $this->relationSegment($relations);
            $relativeFile = $this->relativeHtmlPath(
                $paramsHash,
                $this->localeFromRelations($relations) ?? $this->resolvePresentationItemLocaleFromKey($cacheKey),
            );

            if ($modelSegment !== null) {
                return $this->basePath
                    . '/' . $module
                    . '/' . $route
                    . '/' . $modelSegment
                    . '/' . $id
                    . '/' . $relativeFile;
            }

            return $this->recordDirectory($module, $route, $id) . '/' . $relativeFile;
        }

        return $this->basePath . '/keys/' . hash('sha256', $cacheKey) . '.html';
    }

    /**
     * @param array<string, int|string|array<int|string>> $relations
     */
    protected function relationSegment(array $relations): ?string
    {
        foreach ($this->filterRelationModels($relations) as $modelClass => $ids) {
            if (is_string($modelClass) && $modelClass !== '') {
                return class_basename($modelClass);
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveReadPaths(string $cacheKey, ?string $locale = null): array
    {
        $paths = [];

        if (preg_match(
            '#^[^:]+:([^:]+):([^:]+):presentationItem:([^:]+):([a-f0-9]+)$#',
            $cacheKey,
            $matches,
        )) {
            [, $module, $route, $id, $paramsHash] = $matches;
            $locale ??= $this->resolvePresentationItemLocaleFromKey($cacheKey);

            foreach ($this->presentationItemReadRelativePaths($paramsHash, $locale) as $relativeFile) {
                $paths[] = $this->recordDirectory($module, $route, $id) . '/' . $relativeFile;

                foreach (glob($this->basePath . '/' . $module . '/' . $route . '/*/' . $id . '/' . $relativeFile) ?: [] as $match) {
                    $paths[] = $match;
                }
            }
        }

        $paths[] = $this->basePath . '/keys/' . hash('sha256', $cacheKey) . '.html';

        return array_values(array_unique($paths));
    }

    /**
     * @param array<string, int|string|array<int|string>> $relations
     */
    protected function relationsWithResolvedLocale(string $cacheKey, array $relations): array
    {
        if ($this->localeFromRelations($relations) !== null) {
            return $relations;
        }

        $locale = $this->resolvePresentationItemLocaleFromKey($cacheKey);
        if ($locale === null) {
            return $relations;
        }

        return array_merge($relations, [self::LOCALE_RELATION_KEY => $locale]);
    }

    protected function localeFromRelations(array $relations): ?string
    {
        $locale = $relations[self::LOCALE_RELATION_KEY] ?? null;

        return is_string($locale) && $locale !== '' ? $this->normalizeCacheLocale($locale) : null;
    }

    /**
     * @param array<string, int|string|array<int|string>> $relations
     * @return array<string, int|string|array<int|string>>
     */
    protected function filterRelationModels(array $relations): array
    {
        unset($relations[self::LOCALE_RELATION_KEY]);

        return $relations;
    }

    protected function relativeHtmlPath(string $paramsHash, ?string $locale): string
    {
        $fileName = $paramsHash . '.html';

        if ($locale === null || $locale === '') {
            return $fileName;
        }

        return $this->normalizeCacheLocale($locale) . '/' . $fileName;
    }

    /**
     * @return array<int, string>
     */
    protected function presentationItemReadRelativePaths(string $paramsHash, ?string $locale): array
    {
        $paths = [];

        if ($locale !== null && $locale !== '') {
            $paths[] = $this->relativeHtmlPath($paramsHash, $locale);
        }

        $paths[] = $this->relativeHtmlPath($paramsHash, null);

        return array_values(array_unique($paths));
    }

    protected function resolvePresentationItemLocaleFromKey(string $cacheKey): ?string
    {
        if (! preg_match(
            '#^[^:]+:([^:]+):([^:]+):presentationItem:([^:]+):([a-f0-9]+)$#',
            $cacheKey,
            $matches,
        )) {
            return null;
        }

        [, $module, $route, $id, $paramsHash] = $matches;

        foreach ($this->configuredLocales() as $locale) {
            $locale = $this->normalizeCacheLocale((string) $locale);
            foreach ($this->presentationItemParamVariants() as $extraParams) {
                if ($this->presentationItemParamsHash($locale, $extraParams) === $paramsHash) {
                    return $locale;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function configuredLocales(): array
    {
        $locales = config('cms.locales');
        if (is_array($locales) && $locales !== []) {
            $keys = array_keys($locales);
            if ($keys !== [] && is_string($keys[0] ?? null)) {
                return $keys;
            }

            return array_values(array_filter($locales, static fn ($locale): bool => is_string($locale) && $locale !== ''));
        }

        $fallback = config('app.available_locales', config('app.locales', [config('app.locale', 'en')]));

        return is_array($fallback) ? array_values($fallback) : [(string) $fallback];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function presentationItemParamVariants(): array
    {
        return [
            [],
            ['full' => true],
        ];
    }

    /**
     * @param array<string, mixed> $extraParams
     */
    protected function presentationItemParamsHash(string $locale, array $extraParams = []): string
    {
        $params = array_merge(['locale' => $this->normalizeCacheLocale($locale)], $extraParams);

        return $this->paramsHash($params);
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function paramsHash(array $params): string
    {
        return ! empty($params)
            ? md5(serialize($this->normalizeParams($params)))
            : 'default';
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    protected function normalizeParams(array $params): array
    {
        ksort($params);

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = $this->normalizeParams($value);
            }
        }

        return $params;
    }

    protected function normalizeCacheLocale(string $locale): string
    {
        if (class_exists(CmsPublicPresentationItemCache::class)) {
            return CmsPublicPresentationItemCache::normalizeCacheLocale($locale);
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

    protected function recordDirectory(string $module, string $route, string $id): string
    {
        return $this->basePath . '/' . $module . '/' . $route . '/' . $id;
    }

    protected function metaPath(string $htmlPath): string
    {
        return $htmlPath . '.meta';
    }

    protected function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function readPathIfFresh(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $metaPath = $this->metaPath($path);
        if (is_file($metaPath)) {
            $meta = json_decode((string) file_get_contents($metaPath), true);
            $expiresAt = is_array($meta) ? (int) ($meta['expires_at'] ?? 0) : 0;
            if ($expiresAt > 0 && time() > $expiresAt) {
                $this->deleteFilePair($path);

                return null;
            }
        }

        $contents = file_get_contents($path);

        return is_string($contents) && $contents !== '' ? $contents : null;
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

    protected function deleteDirectoryContents(string $directory): int
    {
        if (! is_dir($directory)) {
            return 0;
        }

        $deleted = 0;
        foreach (glob($directory . '/*') ?: [] as $file) {
            if (is_file($file)) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        @rmdir($directory);

        return $deleted;
    }
}
