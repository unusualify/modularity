<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Illuminate\Support\Facades\Http;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;

class RemoteApiClient
{
    /** @var array<int, int> */
    private array $allowedStatuses = [];

    private readonly RemoteApiLogger $logger;

    public function __construct(
        private readonly RemoteApiConfiguration $configuration,
        private readonly RemoteApiRateLimiter $rateLimiter = new RemoteApiRateLimiter,
        private readonly RemoteApiRequestTracker $requestTracker = new RemoteApiRequestTracker,
        ?RemoteApiLogger $logger = null,
    ) {
        $this->logger = $logger ?? new RemoteApiLogger($configuration);
    }

    public function setAllowedStatuses(array $allowedStatuses): void
    {
        $this->allowedStatuses = $allowedStatuses;
    }

    public function requestTracker(): RemoteApiRequestTracker
    {
        return $this->requestTracker;
    }

    /**
     * @return array{total: int, by_url: array<string, int>}
     */
    public function flushRequestStats(): array
    {
        return $this->requestTracker->flush();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchPaginatedList(string $endpoint, array $query = [], ?string $listPath = null): array
    {
        return $this->fetchPaginatedListResult($endpoint, $query, $listPath)['items'];
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, expected_total: int}
     */
    public function fetchPaginatedListResult(string $endpoint, array $query = [], ?string $listPath = null): array
    {
        $items = [];
        $expectedTotal = 0;

        $this->eachPaginatedListPage(
            $endpoint,
            function (array $chunk, int $page, int $lastPage, int $total) use (&$items, &$expectedTotal): void {
                $items = array_merge($items, $chunk);
                $expectedTotal = max($expectedTotal, $total);
            },
            $query,
            $listPath,
        );

        return [
            'items' => $items,
            'expected_total' => $expectedTotal > 0 ? $expectedTotal : count($items),
        ];
    }

    /**
     * Stream paginated list pages without accumulating the full result set.
     *
     * @param  callable(array<int, array<string, mixed>> $pageItems, int $page, int $lastPage, int $expectedTotal): void  $callback
     */
    public function eachPaginatedListPage(
        string $endpoint,
        callable $callback,
        array $query = [],
        ?string $listPath = null,
    ): void {
        $page = 1;
        $lastPage = 1;
        $expectedTotal = 0;
        $collectedCount = 0;
        $listPath ??= $this->configuration->listPath();
        $fetchedPages = 0;

        do {
            $fetchedPages++;

            if ($fetchedPages > 100) {
                throw RemoteApiSyncException::incompletePaginatedList(
                    max($expectedTotal, $collectedCount),
                    $collectedCount,
                );
            }

            $response = $this->get($endpoint, array_merge($query, ['page' => $page]));
            $chunk = $this->extractList($response, $listPath);
            $collectedCount += count($chunk);

            $meta = data_get($response, $this->configuration->metaPath(), []);
            if (! is_array($meta)) {
                $meta = [];
            }

            $currentPage = (int) data_get($meta, 'current_page', $page);
            $lastPage = max($lastPage, (int) data_get($meta, 'last_page', $currentPage));
            $expectedTotal = max($expectedTotal, (int) data_get($meta, 'total', 0));
            $nextPageUrl = data_get($meta, 'next_page_url');

            $callback($chunk, $currentPage, $lastPage, $expectedTotal);

            $page = $currentPage + 1;
        } while (
            $currentPage < $lastPage
            || (filled($nextPageUrl) && ($expectedTotal === 0 || $collectedCount < $expectedTotal))
        );

        if ($expectedTotal > 0 && $collectedCount < $expectedTotal) {
            throw RemoteApiSyncException::incompletePaginatedList($expectedTotal, $collectedCount);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $endpoint, array $query = [], bool $allowNotFound = false): ?array
    {
        $url = $this->buildUrl($endpoint);
        $query = $this->sanitizeQuery($this->mergeDefaultQuery($query));
        $trackedUrl = $this->buildTrackedUrl($url, $query);
        $startedAt = microtime(true);

        $this->rateLimiter->assertCanRequest($url);

        try {
            $response = Http::timeout($this->configuration->timeout())
                ->withHeaders($this->defaultHeaders())
                ->acceptJson()
                ->get($url, $query);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $status = $response->status();

            $this->rateLimiter->hit($url);
            $this->requestTracker->record($trackedUrl);

            if ($status === 429) {
                $retryAfter = (int) ($response->header('Retry-After') ?: 60);

                $this->logger->logHttpRequest(
                    $trackedUrl,
                    'GET',
                    $durationMs,
                    $status,
                    $this->requestTracker,
                    $this->rateLimiter,
                    retryAfter: $retryAfter,
                );

                throw RemoteApiSyncException::rateLimitExceeded($url, $retryAfter);
            }

            if ($status === 404) {
                $this->logger->logHttpRequest(
                    $trackedUrl,
                    'GET',
                    $durationMs,
                    $status,
                    $this->requestTracker,
                    $this->rateLimiter,
                );

                if ($allowNotFound) {
                    return null;
                }

                throw RemoteApiSyncException::recordNotFound($this->extractNotFoundRemoteId($endpoint));
            }

            if ($response->failed()) {
                $this->logger->logHttpRequest(
                    $trackedUrl,
                    'GET',
                    $durationMs,
                    $status,
                    $this->requestTracker,
                    $this->rateLimiter,
                );

                $response->throw();
            }

            $this->logger->logHttpRequest(
                $trackedUrl,
                'GET',
                $durationMs,
                $status,
                $this->requestTracker,
                $this->rateLimiter,
            );

            return (array) $response->json();
        } catch (RemoteApiSyncException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $this->logger->logHttpRequest(
                $trackedUrl,
                'GET',
                $durationMs,
                null,
                $this->requestTracker,
                $this->rateLimiter,
                exception: $exception,
            );

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $data = [], array $query = [], array $allowedStatuses = []): array
    {
        $url = $this->buildUrl($endpoint);
        $query = $this->sanitizeQuery($this->mergeDefaultQuery($query));
        $trackedUrl = $this->buildTrackedUrl($url, $query);
        $startedAt = microtime(true);

        $this->rateLimiter->assertCanRequest($url);

        $allowedStatuses = array_merge($this->allowedStatuses, $allowedStatuses);

        try {
            $pendingRequest = Http::timeout($this->configuration->timeout())
                ->withHeaders($this->defaultHeaders())
                ->acceptJson()
                ->asJson();

            $response = $query === []
                ? $pendingRequest->post($url, $data)
                : $pendingRequest->post($url . '?' . http_build_query($query), $data);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $status = $response->status();

            $this->rateLimiter->hit($url);
            $this->requestTracker->record($trackedUrl);

            if ($status === 429) {
                $retryAfter = (int) ($response->header('Retry-After') ?: 60);

                $this->logger->logHttpRequest(
                    $trackedUrl,
                    'POST',
                    $durationMs,
                    $status,
                    $this->requestTracker,
                    $this->rateLimiter,
                    retryAfter: $retryAfter,
                );

                throw RemoteApiSyncException::rateLimitExceeded($url, $retryAfter);
            }

            if ($response->failed() && ! in_array($status, $allowedStatuses)) {
                $this->logger->logHttpRequest(
                    $trackedUrl,
                    'POST',
                    $durationMs,
                    $status,
                    $this->requestTracker,
                    $this->rateLimiter,
                );

                $response->throw();
            }

            $this->logger->logHttpRequest(
                $trackedUrl,
                'POST',
                $durationMs,
                $status,
                $this->requestTracker,
                $this->rateLimiter,
            );

            return (array) [
                'status_code' => $status,
                ...(array) $response->json(),
            ];
        } catch (RemoteApiSyncException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $this->logger->logHttpRequest(
                $trackedUrl,
                'POST',
                $durationMs,
                null,
                $this->requestTracker,
                $this->rateLimiter,
                exception: $exception,
            );

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getItem(string $endpoint, array $query = [], ?string $itemPath = null): ?array
    {
        $response = $this->get($endpoint, $query, allowNotFound: true);

        if ($response === null) {
            return null;
        }

        $itemPath ??= $this->configuration->itemPath();
        $item = data_get($response, $itemPath);

        return is_array($item) ? $item : null;
    }

    /**
     * @param array<string, mixed> $response
     * @return array<int, array<string, mixed>>
     */
    public function extractList(array $response, string $listPath): array
    {
        $items = data_get($response, $listPath, []);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, fn ($item) => is_array($item)));
    }

    public function buildUrl(string $endpoint): string
    {
        return $this->configuration->baseUrl() . '/' . ltrim($endpoint, '/');
    }

    /**
     * @return array<string, string>
     */
    private function defaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'Modularous RemoteApi Client',
        ];

        if ($token = $this->configuration->token()) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }

    /**
     * @return array<string, mixed>
     */
    private function mergeDefaultQuery(array $query): array
    {
        $skipSyncIncludes = (bool) ($query['_skip_sync_includes'] ?? false);
        unset($query['_skip_sync_includes']);

        $defaults = $this->configuration->httpQuery();

        if (! $skipSyncIncludes && ! array_key_exists('include', $query)) {
            $includes = $this->configuration->includes();

            if ($includes !== []) {
                $defaults['include'] = implode(',', $includes);
            }
        }

        return array_merge($defaults, $query);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function buildTrackedUrl(string $url, array $query): string
    {
        if ($query === []) {
            return $url;
        }

        ksort($query);

        return $url . '?' . http_build_query($query);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function sanitizeQuery(array $query): array
    {
        return array_filter(
            $query,
            static fn ($value) => $value !== null && $value !== ''
        );
    }

    private function extractNotFoundRemoteId(string $endpoint): int|string
    {
        if (preg_match('#/(\d+)$#', trim($endpoint, '/'), $matches) === 1) {
            return (int) $matches[1];
        }

        return $endpoint;
    }
}
