<?php

namespace Unusualify\Modularous\Services;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Predis\Connection\ConnectionException;
use Predis\Connection\Resource\Exception\StreamInitException;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\Cache\PresentationUrlCacheKey;
use Unusualify\Modularous\Services\Cache\PresentationUrlCacheKeyResolver;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Services\Concerns\CacheHelpers;
use Unusualify\Modularous\Services\Concerns\CacheInvalidation;
use Unusualify\Modularous\Services\Concerns\CacheTags;

class ModularousCacheService
{
    use CacheTags,
        CacheInvalidation,
        CacheHelpers;

    /**
     * Cache configuration.
     */
    protected array $config;

    /**
     * Redis connection status.
     */
    protected bool $connected = false;

    /**
     * Cache store instance.
     */
    protected $store;

    /**
     * Filesystem stale cache (SWR).
     */
    protected StaleFileCache $staleFileCache;

    /**
     * URL-keyed public presentation HTML store (file-primary by default).
     */
    protected UrlPresentationCacheStoreInterface $urlPresentationCacheStore;

    /**
     * Create a new cache service instance.
     */
    public function __construct()
    {
        $this->config = config('modularous.cache', []);

        $stalePath = (string) ($this->config['presentationItem']['model']['stale_path']
            ?? $this->config['swr']['presentationItem']['stale_path']
            ?? storage_path('framework/cache/modularous-stale'));
        $this->staleFileCache = new StaleFileCache(
            $stalePath,
            (int) ($this->config['presentationItem']['stale_ttl'] ?? $this->config['swr']['stale_ttl'] ?? 86400),
        );

        $this->urlPresentationCacheStore = $this->resolveUrlPresentationCacheStore();

        $driverName = $this->getDriver();

        if ($driverName === 'redis') {
            if (! extension_loaded('redis')) {
                logger()->error('Redis extension is not installed on php.ini on modularous cache');
                $this->connected = false;
            } else {
                try {
                    $redis = Redis::connection('cache');
                    $redis->ping();
                    if (! $redis->ping()) {
                        logger()->error('Redis connection failed on modularous cache');
                    } else {
                        $this->connected = true;
                    }
                } catch (ConnectionException $e) {
                    logger()->error('Redis connection failed with connection exception on modularous cache: ' . $e->getMessage());
                } catch (StreamInitException $e) {
                    logger()->error('Redis connection failed with stream init exception on modularous cache: ' . $e->getMessage());
                } catch (\Exception $e) {
                    logger()->error('Redis connection failed with exception on modularous cache: ' . $e->getMessage(), ['exception' => get_class($e), 'trace' => $e->getTraceAsString()]);
                }
            }
        } elseif ($driverName === 'memcached') {
            try {
                if (! extension_loaded('memcached')) {
                    logger()->error('Memcached extension is not installed on php.ini on modularous cache');
                    $this->connected = false;
                } else {
                    $memcached = Cache::store('memcached')->getStore()->getMemcached();
                    if (! $memcached->getStats()) {
                        logger()->error('Memcached connection failed on modularous cache');
                    } else {
                        $this->connected = true;
                    }
                }
            } catch (\Exception $e) {
                logger()->error('Memcached connection failed with exception on modularous cache: ' . $e->getMessage(), ['exception' => get_class($e), 'trace' => $e->getTraceAsString()]);
            }
        } else {
            $this->connected = true;
        }

        $this->store = Cache::store($this->connected ? $driverName : 'array');

        // Detect Laravel version and tag support
        $this->detectTagSupport();
    }

    /**
     * Get the cache configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get the cache driver.
     */
    public function getDriver(): string
    {
        return $this->config['driver'] ?? 'redis';
    }

    protected function detectTagSupport(): void
    {
        $laravelVersion = app()->version();

        // Laravel 10+ has known tag issues
        // if (version_compare($laravelVersion, '10.0', '>=')) {
        //     logger()->warning('Laravel 10+ cache tags have known issues. Consider using predis or disabling tags.');
        // }

        // Test if tags actually work
        try {
            Cache::tags(['test'])->put('test-key', 'test', 10);
            $result = Cache::tags(['test'])->get('test-key');
            Cache::tags(['test'])->flush();
            $stillExists = Cache::tags(['test'])->get('test-key');

            if ($result === 'test' && $stillExists !== null) {
                logger()->error('Cache tags flush is not working properly!');
                // Force disable tags
                $this->config['use_tags'] = false;
            }
        } catch (\Exception $e) {
            // logger()->error('Cache tags test failed: ' . $e->getMessage());
            $this->config['use_tags'] = false;
        }
    }

    /**
     * Get the cache store instance.
     */
    public function getStore(): Repository
    {
        return $this->store;
    }

    public function getStaleFileCache(): StaleFileCache
    {
        return $this->staleFileCache;
    }

    public function getUrlPresentationCacheStore(): UrlPresentationCacheStoreInterface
    {
        return $this->urlPresentationCacheStore;
    }

    /**
     * @deprecated Use getUrlPresentationCacheStore() — returns underlying file cache when driver=file.
     */
    public function getUrlKeyedStaleCache(): UrlKeyedStaleCache
    {
        if ($this->urlPresentationCacheStore instanceof FileUrlPresentationCacheDriver) {
            return $this->urlPresentationCacheStore->underlyingFileCache();
        }

        throw new \RuntimeException(
            'getUrlKeyedStaleCache() is only available when presentationItem.url.driver is file.',
        );
    }

    protected function resolveUrlPresentationCacheStore(): UrlPresentationCacheStoreInterface
    {
        $driver = (string) ($this->config['presentationItem']['url']['driver'] ?? 'file');

        return match ($driver) {
            'file', 'shared_file' => new FileUrlPresentationCacheDriver($this->createUrlKeyedStaleCache()),
            default => new FileUrlPresentationCacheDriver($this->createUrlKeyedStaleCache()),
        };
    }

    protected function createUrlKeyedStaleCache(): UrlKeyedStaleCache
    {
        $urlStalePath = (string) ($this->config['presentationItem']['url']['base_path']
            ?? $this->config['resilience']['url_stale']['base_path']
            ?? storage_path('framework/cache/modularous-stale-by-url'));

        return new UrlKeyedStaleCache(
            $urlStalePath,
            (int) ($this->config['presentationItem']['stale_ttl'] ?? $this->config['resilience']['url_stale']['stale_ttl'] ?? 604800),
        );
    }

    /**
     * Active public presentationItem store: url | model | none.
     */
    public function getPresentationCacheStore(): string
    {
        $store = (string) ($this->config['presentationItem']['store'] ?? 'url');

        return in_array($store, ['url', 'model', 'none'], true) ? $store : 'url';
    }

    public function isPresentationCacheEnabled(): bool
    {
        return $this->getPresentationCacheStore() !== 'none';
    }

    public function isUrlStaleEnabled(): bool
    {
        return $this->getPresentationCacheStore() === 'url';
    }

    public function isModelStaleEnabled(): bool
    {
        return $this->getPresentationCacheStore() === 'model';
    }

    public function isUrlStaleServeFirst(): bool
    {
        return $this->isUrlStaleEnabled()
            && (bool) ($this->config['presentationItem']['serve_first']
                ?? $this->config['resilience']['url_stale']['serve_first']
                ?? true);
    }

    public function getUrlStaleTtl(): int
    {
        return (int) ($this->config['presentationItem']['stale_ttl']
            ?? $this->config['resilience']['url_stale']['stale_ttl']
            ?? 604800);
    }

    /**
     * Route/type cache toggle from config only — does not require Redis connectivity.
     */
    public function isCacheTypeConfigured(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool
    {
        if (! ($this->config['enabled'] ?? true)) {
            return false;
        }

        $defaultBehavior = $this->config['all_modules'] ?? false;
        $defaultModuleRouteBehavior = $this->config['all_module_routes'] ?? $defaultBehavior;
        $defaultTypeBehavior = isset($this->config['default_types']) ? $this->config['default_types'][$type] ?? true : true;

        if ($moduleName !== null) {
            $moduleConfig = $this->config['modules'][$moduleName] ?? [];
            $moduleEnabled = $moduleConfig['enabled'] ?? $defaultBehavior;

            if (! $moduleEnabled) {
                return false;
            }

            if ($moduleRouteName !== null && isset($moduleConfig['routes'][$moduleRouteName])) {
                $moduleRouteConfig = $moduleConfig['routes'][$moduleRouteName] ?? [];
                $moduleRouteEnabled = $moduleRouteConfig['enabled'] ?? $defaultModuleRouteBehavior;

                if (! $moduleRouteEnabled) {
                    return false;
                }

                if ($type !== null && isset($moduleRouteConfig['types'][$type])) {
                    return (bool) ($moduleRouteConfig['types'][$type] ?? $defaultTypeBehavior);
                }

                return $moduleRouteEnabled;
            } elseif ($moduleRouteName !== null) {
                return $defaultBehavior;
            }

            return $moduleEnabled;
        }

        return true;
    }

    /**
     * Whether stale entries use the filesystem driver (required for presentationItem SWR).
     */
    public function usesFileStaleStore(?string $type = null): bool
    {
        if ($type === 'presentationItem') {
            return $this->getPresentationCacheStore() === 'model'
                && ($this->config['swr']['presentationItem']['stale_driver'] ?? 'file') === 'file';
        }

        if ($type === null) {
            return ($this->config['swr']['presentationItem']['stale_driver'] ?? 'file') === 'file';
        }

        return false;
    }

    /**
     * Whether filesystem stale storage is available (independent of Redis/Memcached connectivity).
     */
    public function isStaleStorageEnabled(?string $type = null): bool
    {
        if (! ($this->config['enabled'] ?? true)) {
            return false;
        }

        if ($type === 'presentationItem') {
            return $this->getPresentationCacheStore() === 'model';
        }

        return $this->usesFileStaleStore($type);
    }

    /**
     * Get the cache prefix.
     */
    public function getPrefix(): string
    {
        return $this->config['prefix'] ?? 'modularous';
    }

    /**
     * Check if caching is enabled globally or for a specific module.
     */
    public function isEnabled(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool
    {
        if (! $this->connected) {
            return false;
        }

        // Check global enabled flag
        if (! ($this->config['enabled'] ?? true)) {
            return false;
        }

        $defaultBehavior = $this->config['all_modules'] ?? false;
        $defaultModuleRouteBehavior = $this->config['all_module_routes'] ?? $defaultBehavior;
        $defaultTypeBehavior = isset($this->config['default_types']) ? $this->config['default_types'][$type] ?? true : true;

        // Check module-specific enabled flag
        if ($moduleName !== null) {
            $moduleConfig = $this->config['modules'][$moduleName] ?? [];

            $moduleEnabled = $moduleConfig['enabled'] ?? $defaultBehavior;

            if (! $moduleEnabled) {
                return false;
            }

            if ($moduleRouteName !== null && isset($moduleConfig['routes']) && isset($moduleConfig['routes'][$moduleRouteName])) {
                $moduleRouteConfig = $moduleConfig['routes'][$moduleRouteName] ?? [];
                $moduleRouteEnabled = $moduleRouteConfig['enabled'] ?? $defaultModuleRouteBehavior;

                if (! $moduleRouteEnabled) {
                    return false;
                }

                if ($type !== null && isset($moduleRouteConfig['types'][$type])) {
                    return $moduleRouteConfig['types'][$type] ?? $defaultTypeBehavior;
                }

                return $moduleRouteEnabled;

            } elseif ($moduleRouteName !== null) {
                return $defaultBehavior;
            }

            return $moduleEnabled;
        }

        return true;
    }

    /**
     * Route-level cache config merge for a module route.
     */
    public function getRouteCacheConfig(?string $moduleName, ?string $moduleRouteName): array
    {
        if ($moduleName === null || $moduleRouteName === null) {
            return [];
        }

        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);
        $moduleConfig = $this->config['modules'][$moduleName] ?? [];

        return $moduleConfig['routes'][$moduleRouteName] ?? [];
    }

    /**
     * URL presentation cache key strategy for a module route.
     *
     * @see \Unusualify\Modularous\Services\Cache\PresentationUrlCacheKey
     */
    public function getPresentationCacheKeyStrategy(?string $moduleName, ?string $moduleRouteName): string
    {
        $strategy = (string) ($this->getRouteCacheConfig($moduleName, $moduleRouteName)['presentation_cache_key'] ?? PresentationUrlCacheKey::STRATEGY_PATH_ONLY);

        return in_array($strategy, [
            PresentationUrlCacheKey::STRATEGY_PATH_ONLY,
            PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY,
            PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST,
        ], true) ? $strategy : PresentationUrlCacheKey::STRATEGY_PATH_ONLY;
    }

    /**
     * @return list<string>
     */
    public function getPresentationCacheQueryAllowlist(?string $moduleName, ?string $moduleRouteName): array
    {
        $allowlist = $this->getRouteCacheConfig($moduleName, $moduleRouteName)['presentation_cache_query'] ?? [];

        return array_values(array_map('strval', (array) $allowlist));
    }

    public function getPresentationUrlCacheKeyResolver(): PresentationUrlCacheKeyResolver
    {
        return new PresentationUrlCacheKeyResolver($this, $this->urlPresentationCacheStore);
    }

    /**
     * Whether the observer should auto-invalidate/warm a cache type for a route.
     */
    public function shouldAutoInvalidate(?string $moduleName, ?string $moduleRouteName, ?string $type = null): bool
    {
        if ($moduleName === null || $moduleRouteName === null) {
            return true;
        }

        $observerAuto = (bool) ($this->config['observer']['auto_invalidate'] ?? true);
        if (! $observerAuto) {
            return false;
        }

        $routeConfig = $this->getRouteCacheConfig($moduleName, $moduleRouteName);
        $globalManual = (bool) ($this->config['manual_purge'] ?? false);
        $routeManual = (bool) ($routeConfig['manual_purge'] ?? $globalManual);

        if ($type === null) {
            return ! $routeManual;
        }

        $purgeTypes = $routeConfig['purge'] ?? [];
        if (array_key_exists($type, $purgeTypes)) {
            return ! (bool) $purgeTypes[$type];
        }

        return ! $routeManual;
    }

    /**
     * Whether admin purge/warm actions should be exposed for a route.
     */
    public function hasAdminCacheActions(?string $moduleName, ?string $moduleRouteName): bool
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return false;
        }

        $routeConfig = $this->getRouteCacheConfig($moduleName, $moduleRouteName);

        return (bool) ($routeConfig['admin_cache_actions'] ?? false);
    }

    /**
     * Check if cache tags are supported and enabled.
     */
    public function usesTags(): bool
    {
        if (! ($this->config['use_tags'] ?? true)) {
            return false;
        }

        // Check if the cache driver supports tags
        try {
            $this->store->tags(['test']);

            return true;
        } catch (\BadMethodCallException $e) {
            return false;
        }
    }

    /**
     * Whether stale-while-revalidate is enabled for a cache type and route.
     */
    public function isSwrEnabled(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool
    {
        if ($type === 'presentationItem' || $type === null) {
            if (! $this->isPresentationCacheEnabled()) {
                return false;
            }

            $swrEnabled = (bool) ($this->config['presentationItem']['swr'] ?? $this->config['swr']['enabled'] ?? false);
            if (! $swrEnabled) {
                return false;
            }
        } elseif (! ($this->config['swr']['enabled'] ?? false)) {
            return false;
        }

        if ($type !== null && $type !== 'presentationItem') {
            $typeEnabled = $this->config['swr']['types'][$type] ?? true;
            if (! $typeEnabled) {
                return false;
            }
        }

        if ($moduleName !== null && $moduleRouteName !== null && $type !== null) {
            if ($type === 'presentationItem' && $this->getPresentationCacheStore() === 'url') {
                return $this->isCacheTypeConfigured($moduleName, $moduleRouteName, $type);
            }

            return $this->isEnabled($moduleName, $moduleRouteName, $type);
        }

        return true;
    }

    /**
     * Stale TTL for SWR entries (seconds).
     */
    public function getStaleTtl(?string $type = null): int
    {
        if ($type === 'presentationItem' || $type === null) {
            return (int) ($this->config['presentationItem']['stale_ttl'] ?? $this->config['swr']['stale_ttl'] ?? 86400);
        }

        return (int) ($this->config['swr']['stale_ttl'] ?? 86400);
    }

    /**
     * Whether the cache revalidate webhook endpoint is enabled.
     */
    public function isWebhookEnabled(): bool
    {
        return (bool) ($this->config['webhook']['enabled'] ?? false);
    }

    /**
     * Shared secret for webhook HMAC verification.
     */
    public function getWebhookSecret(): ?string
    {
        $secret = $this->config['webhook']['secret'] ?? null;

        return is_string($secret) && $secret !== '' ? $secret : null;
    }

    /**
     * Get TTL for a specific cache type and optional module.
     */
    public function getTtl(string $type, ?string $moduleName = null, ?string $moduleRouteName = null): int
    {
        // Check module-specific TTL first
        if ($moduleName !== null) {
            $moduleConfig = $this->config['modules'][$moduleName] ?? [];

            if (isset($moduleConfig['routes']) && isset($moduleConfig['routes'][$moduleRouteName])) {
                $moduleRouteConfig = $moduleConfig['routes'][$moduleRouteName] ?? [];
                $moduleRouteTtl = $moduleRouteConfig['ttl'][$type] ?? null;

                if ($moduleRouteTtl !== null) {
                    return (int) $moduleRouteTtl;
                }
            }

            if (isset($moduleConfig['ttl'][$type])) {
                return (int) $moduleConfig['ttl'][$type];
            }
        }

        // Fall back to global TTL
        return (int) ($this->config['ttl'][$type] ?? 300);
    }

    /**
     * Resolve enabled cache types for admin actions (manual purge routes).
     *
     * @return array<string, bool>
     */
    public function resolveManualCacheTypes(string $moduleName, string $moduleRouteName): array
    {
        $types = ['counts', 'index', 'record', 'formItem', 'formattedItem', 'presentationItem'];
        $resolved = [];

        foreach ($types as $type) {
            $resolved[$type] = $this->isEnabled($moduleName, $moduleRouteName, $type)
                && ! $this->shouldAutoInvalidate($moduleName, $moduleRouteName, $type);
        }

        return $resolved;
    }

    /**
     * Normalize a cache types array from request input.
     *
     * @param array<int, string>|string $typesInput
     * @return array<string, bool>
     */
    public function normalizeCacheTypesInput(array|string $typesInput, string $moduleName, string $moduleRouteName): array
    {
        $allTypes = ['counts', 'index', 'record', 'formItem', 'formattedItem', 'presentationItem'];

        if ($typesInput === 'all') {
            $types = array_fill_keys($allTypes, true);
        } else {
            $types = array_fill_keys($allTypes, false);
            foreach ((array) $typesInput as $type) {
                if (is_string($type) && in_array($type, $allTypes, true)) {
                    $types[$type] = true;
                }
            }
        }

        foreach ($allTypes as $type) {
            if ($types[$type] && ! $this->isEnabled($moduleName, $moduleRouteName, $type)) {
                $types[$type] = false;
            }
        }

        return $types;
    }

    /**
     * Normalize parameters for consistent hashing.
     */
    protected function normalizeParams(array $params): array
    {
        // Sort array keys recursively
        ksort($params);

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = $this->normalizeParams($value);
            }
        }

        return $params;
    }

    /**
     * Generate a cache key.
     *
     * Format: {prefix}:{module}:{type}:{params_hash}
     *
     * Note: When using tags, Laravel adds its own namespace prefix to the actual Redis key.
     * This key is used consistently for both storing and retrieving, so the tag prefix is handled automatically.
     */
    public function generateCacheKey(string $moduleName, string $moduleRouteName, string $type, array $params = []): string
    {
        $prefix = $this->getPrefix();
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        // Generate a hash of the parameters for uniqueness
        $paramsHash = ! empty($params) ? md5(serialize($this->normalizeParams($params))) : 'default';

        return "{$prefix}:{$moduleName}:{$moduleRouteName}:{$type}:{$paramsHash}";
    }

    /**
     * Get cache statistics for a module.
     *
     * When using tags, this reads from the tag's entry set in Redis.
     * Without tags, it scans for keys matching the pattern.
     */
    public function getStats(?string $module = null): array
    {
        $prefix = $this->getPrefix();
        $redisPrefix = config('database.redis.options.prefix', '');

        if ($this->usesTags()) {
            return $this->getTaggedCacheStats($module, $prefix, $redisPrefix);
        }

        return $this->getNonTaggedCacheStats($module, $prefix, $redisPrefix);
    }

    /**
     * Get stats for tagged cache by reading the tag entry sets.
     */
    protected function getTaggedCacheStats(?string $module, string $prefix, string $redisPrefix): array
    {
        $keys = [];

        try {
            $redis = Redis::connection('cache');

            // Build tag key pattern to find tag entry sets
            $tagPattern = $module
                ? "{$redisPrefix}tag:{$prefix}:{$module}:entries"
                : "{$redisPrefix}tag:{$prefix}:*:entries";

            $cursor = 0;
            $tagKeys = [];

            // Find all tag entry sets
            do {
                [$cursor, $foundKeys] = $redis->scan($cursor, 'MATCH', $tagPattern, 'COUNT', 100);
                $tagKeys = array_merge($tagKeys, $foundKeys ?? []);
            } while ($cursor != 0);

            // Read entries from each tag set
            foreach ($tagKeys as $tagKey) {
                $entries = $redis->zRange($tagKey, 0, -1);
                foreach ($entries as $entry) {
                    // Entry format: {tag_namespace_hash}:{our_cache_key}
                    // Extract our cache key (after the first colon following the hash)
                    if (preg_match('/^[a-f0-9]+:(.+)$/', $entry, $matches)) {
                        $keys[] = $matches[1];
                    } else {
                        $keys[] = $entry;
                    }
                }
            }

            $keys = array_unique($keys);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'keys_count' => 0,
                'using_tags' => true,
            ];
        }

        return [
            'keys_count' => count($keys),
            'keys' => $keys,
            'using_tags' => true,
        ];
    }

    /**
     * Get stats for non-tagged cache by scanning Redis keys.
     */
    protected function getNonTaggedCacheStats(?string $module, string $prefix, string $redisPrefix): array
    {
        $pattern = $module
            ? "{$prefix}:{$module}:*"
            : "{$prefix}:*";

        $keys = [];

        try {
            $redis = Redis::connection('cache');
            $cursor = 0;

            do {
                [$cursor, $foundKeys] = $redis->scan($cursor, 'MATCH', $redisPrefix . $pattern, 'COUNT', 100);
                $keys = array_merge($keys, $foundKeys ?? []);
            } while ($cursor != 0);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'keys_count' => 0,
                'using_tags' => false,
            ];
        }

        return [
            'keys_count' => count($keys),
            'keys' => array_map(fn ($key) => str_replace($redisPrefix, '', $key), $keys),
            'using_tags' => false,
        ];
    }
}
