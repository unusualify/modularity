<?php

namespace Unusualify\Modularous\Services\Concerns;

use Closure;
use Illuminate\Cache\Repository;

/**
 * Cache helper methods.
 *
 * @requires property $store - Cache store instance
 * @requires method usesTags() - Check if tags are supported
 * @requires method isEnabled() - Check if caching is enabled
 * @requires method getPrefix() - Get cache prefix
 */
trait CacheHelpers
{
    use CacheTags, CacheInvalidation;

    /**
     * Get the cache store instance.
     */
    abstract protected function getStore(): Repository;

    /**
     * Get the cache prefix.
     */
    abstract protected function getPrefix(): string;

    /**
     * Check if cache tags are supported.
     */
    abstract protected function usesTags(): bool;

    /**
     * Check if caching is enabled.
     */
    abstract protected function isEnabled(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool;

    /**
     * Remember a value in cache with relationship tags for granular invalidation.
     *
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param Closure $callback Value generator
     * @param string|null $module Module name
     * @param string|null $routeName Route name (submodule)
     * @param array $relations Related models ['ModelClass' => id, ...]
     */
    public function rememberWithRelations(string $key, int $ttl, Closure $callback, ?string $moduleName = null, ?string $moduleRouteName = null, array $relations = [], ?string $type = null)
    {
        // Skip cache if disabled
        if (! $this->isEnabled($moduleName, $moduleRouteName, $type)) {
            return $callback();
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            // Add relationship tags for granular invalidation
            if (! empty($relations)) {
                $tags = array_merge($tags, $this->generateRelationTags($relations));
            }

            return $this->getStore()
                ->tags($tags)
                ->remember($key, $ttl, $callback);
        }

        return $this->getStore()->remember($key, $ttl, $callback);
    }

    /**
     * Remember a value in cache.
     */
    public function remember(string $key, int $ttl, Closure $callback, ?string $moduleName = null, ?string $moduleRouteName = null)
    {
        // Skip cache if disabled
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return $callback();
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true)
                : $this->getModuleTags($moduleName, onlyModule: false);

            return $this->getStore()
                ->tags($tags)
                ->remember($key, $ttl, $callback);
        }

        return $this->getStore()->remember($key, $ttl, $callback);
    }

    /**
     * Remember a value in cache forever.
     */
    public function rememberForever(string $key, Closure $callback, ?string $moduleName = null, ?string $moduleRouteName = null)
    {
        // Skip cache if disabled
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return $callback();
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            return $this->getStore()
                ->tags($tags)
                ->rememberForever($key, $callback);
        }

        return $this->getStore()->rememberForever($key, $callback);
    }

    /**
     * Get a value from cache.
     */
    public function get(string $key, $default = null, ?string $moduleName = null, ?string $moduleRouteName = null)
    {
        return $this->getWithRelations($key, $default, $moduleName, $moduleRouteName);
    }

    /**
     * Get a value from cache with relationship tags (must match {@see putWithRelations()} / {@see rememberWithRelations()}).
     *
     * @param array<string, int|string|array<int|string>> $relations
     */
    public function getWithRelations(
        string $key,
        $default = null,
        ?string $moduleName = null,
        ?string $moduleRouteName = null,
        array $relations = [],
        ?string $type = null,
    ) {
        if (! $this->isEnabled($moduleName, $moduleRouteName, $type)) {
            return $default;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            if (! empty($relations)) {
                $tags = array_merge($tags, $this->generateRelationTags($relations));
            }

            return $this->getStore()
                ->tags($tags)
                ->get($key, $default);
        }

        return $this->getStore()->get($key, $default);
    }

    /**
     * Put a value in cache.
     */
    public function put(string $key, $value, int $ttl, ?string $moduleName = null, ?string $moduleRouteName = null): bool
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return false;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            return $this->getStore()
                ->tags($tags)
                ->put($key, $value, $ttl);
        }

        return $this->getStore()->put($key, $value, $ttl);
    }

    /**
     * Put a value in cache with relationship tags for granular invalidation.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @param string|null $module Module name
     * @param string|null $routeName Route name (submodule)
     * @param array $relations Related models ['ModelClass' => id, ...]
     */
    public function putWithRelations(string $key, $value, int $ttl, ?string $moduleName = null, ?string $moduleRouteName = null, array $relations = [], ?string $type = null): bool
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName, $type)) {
            return false;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            // Add relationship tags for granular invalidation
            if (! empty($relations)) {
                $tags = array_merge($tags, $this->generateRelationTags($relations));
            }

            return $this->getStore()
                ->tags($tags)
                ->put($key, $value, $ttl);
        }

        return $this->getStore()->put($key, $value, $ttl);
    }

    /**
     * Check if a key exists in cache.
     */
    public function has(string $key, ?string $moduleName = null, ?string $moduleRouteName = null): bool
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return false;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            return $this->getStore()
                ->tags($tags)
                ->has($key);
        }

        return $this->getStore()->has($key);
    }

    /**
     * Forget a specific cache key.
     */
    public function forget(string $key, ?string $moduleName = null, ?string $moduleRouteName = null): bool
    {
        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true)
                : $this->getModuleTags($moduleName, onlyModule: true);

            return $this->getStore()
                ->tags($tags)
                ->forget($key);
        }

        return $this->getStore()->forget($key);
    }

    /**
     * Flush all modularous caches.
     */
    public function flush(): bool
    {
        if ($this->usesTags()) {
            // Flush the main modularous tag
            $this->getStore()->tags([$this->getPrefix()])->flush();

            return true;
        }

        // Fallback: invalidate by pattern
        return $this->invalidateByPattern("{$this->getPrefix()}:*") > 0;
    }
}
