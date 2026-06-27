<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

class RemoteApiCache
{
    private readonly RemoteApiLogger $logger;

    public function __construct(
        private readonly RemoteApiConfiguration $configuration,
        ?RemoteApiLogger $logger = null,
    ) {
        $this->logger = $logger ?? new RemoteApiLogger($configuration);
    }

    /**
     * Stable hash for cache keys regardless of array key order.
     */
    public static function queryHash(array $query): string
    {
        $normalized = array_filter(
            $query,
            static fn ($value) => $value !== null && $value !== ''
        );
        ksort($normalized);

        return md5((string) json_encode($normalized, JSON_THROW_ON_ERROR));
    }

    /**
     * @return mixed
     */
    public function remember(string $key, callable $callback)
    {
        if (! $this->configuration->cacheEnabled()) {
            return $callback();
        }

        return $this->store()->remember(
            $this->prefixKey($key),
            $this->configuration->cacheTtl(),
            $callback
        );
    }

    /**
     * Like {@see remember()} but skips persisting empty array results so transient API failures are retried.
     *
     * @return mixed
     */
    public function rememberNonEmpty(string $key, callable $callback)
    {
        if (! $this->configuration->cacheEnabled()) {
            return $callback();
        }

        $prefixedKey = $this->prefixKey($key);
        $store = $this->store();

        if ($store->has($prefixedKey)) {
            return $store->get($prefixedKey);
        }

        $result = $callback();

        if (is_array($result) && $result !== []) {
            $store->put($prefixedKey, $result, $this->configuration->cacheTtl());
        }

        return $result;
    }

    /**
     * Cache paginated catalog rows with expected total validation.
     * Legacy flat-list cache entries are discarded automatically.
     *
     * @return array<int, array<string, mixed>>
     */
    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getPaginatedCatalogIfValid(string $key): ?array
    {
        if (! $this->configuration->cacheEnabled()) {
            return null;
        }

        $prefixedKey = $this->prefixKey($key);
        $store = $this->store();

        if (! $store->has($prefixedKey)) {
            return null;
        }

        $cached = $store->get($prefixedKey);

        if (! $this->isValidPaginatedCatalogCache($cached)) {
            $store->forget($prefixedKey);

            return null;
        }

        /** @var array<int, array<string, mixed>> */
        return $cached['items'];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function putPaginatedCatalog(string $key, int $expectedTotal, array $items): void
    {
        if (! $this->configuration->cacheEnabled() || $items === []) {
            return;
        }

        $this->store()->put(
            $this->prefixKey($key),
            [
                'expected_total' => $expectedTotal,
                'items' => $items,
            ],
            $this->configuration->cacheTtl(),
        );
    }

    public function rememberPaginatedCatalog(string $key, callable $callback): array
    {
        if (! $this->configuration->cacheEnabled()) {
            /** @var array<int, array<string, mixed>> */
            return $callback();
        }

        $prefixedKey = $this->prefixKey($key);
        $store = $this->store();

        if ($store->has($prefixedKey)) {
            $cached = $store->get($prefixedKey);

            if ($this->isValidPaginatedCatalogCache($cached)) {
                $this->logger->logCacheAccess($key, 'hit', count($cached['items']));

                /** @var array<int, array<string, mixed>> */
                return $cached['items'];
            }

            $store->forget($prefixedKey);
        }

        $this->logger->logCacheAccess($key, 'miss');

        /** @var array{items: array<int, array<string, mixed>>, expected_total: int}|array<int, array<string, mixed>> $result */
        $result = $callback();

        if (is_array($result) && isset($result['items'], $result['expected_total']) && is_array($result['items'])) {
            if ($result['items'] === []) {
                return [];
            }

            $store->put($prefixedKey, [
                'expected_total' => (int) $result['expected_total'],
                'items' => $result['items'],
            ], $this->configuration->cacheTtl());

            return $result['items'];
        }

        /** @var array<int, array<string, mixed>> $items */
        $items = is_array($result) ? $result : [];

        if ($items === []) {
            return $items;
        }

        $store->put($prefixedKey, [
            'expected_total' => count($items),
            'items' => $items,
        ], $this->configuration->cacheTtl());

        return $items;
    }

    private function isValidPaginatedCatalogCache(mixed $cached): bool
    {
        if (! is_array($cached) || ! isset($cached['items'], $cached['expected_total'])) {
            return false;
        }

        if (! is_array($cached['items'])) {
            return false;
        }

        $expectedTotal = (int) $cached['expected_total'];

        return $expectedTotal > 0 && count($cached['items']) === $expectedTotal;
    }

    public function forget(string $key): void
    {
        if (! $this->configuration->cacheEnabled()) {
            return;
        }

        $this->store()->forget($this->prefixKey($key));
    }

    public function flush(int|string|null $remoteId = null): void
    {
        if (! $this->configuration->cacheEnabled()) {
            return;
        }

        if ($remoteId !== null) {
            $this->forget('record:' . $remoteId);
            $this->forget('preview:' . $remoteId);

            return;
        }

        if ($this->supportsTags()) {
            Cache::tags([$this->configuration->cacheTag()])->flush();

            return;
        }

        Cache::put($this->versionKey(), (string) time(), now()->addYears(10));
    }

    private function store(): CacheRepository
    {
        if ($this->supportsTags()) {
            return Cache::tags([$this->configuration->cacheTag()]);
        }

        return Cache::store();
    }

    private function supportsTags(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }

    private function versionKey(): string
    {
        return sprintf(
            'remote-api:%s:%s:cache-version',
            snakeCase($this->configuration->moduleName()),
            snakeCase($this->configuration->routeName),
        );
    }

    private function cacheVersion(): string
    {
        if ($this->supportsTags()) {
            return 'tagged';
        }

        return (string) Cache::get($this->versionKey(), '0');
    }

    private function prefixKey(string $key): string
    {
        return sprintf(
            'remote-api:%s:%s:%s:%s',
            snakeCase($this->configuration->moduleName()),
            snakeCase($this->configuration->routeName),
            $this->cacheVersion(),
            $key
        );
    }
}
