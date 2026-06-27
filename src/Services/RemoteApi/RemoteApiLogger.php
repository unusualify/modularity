<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Illuminate\Support\Facades\Log;

class RemoteApiLogger
{
    public function __construct(
        private readonly ?RemoteApiConfiguration $configuration = null,
    ) {}

    public function logHttpRequest(
        string $url,
        string $method,
        int $durationMs,
        ?int $status = null,
        ?RemoteApiRequestTracker $requestTracker = null,
        ?RemoteApiRateLimiter $rateLimiter = null,
        ?\Throwable $exception = null,
        ?int $retryAfter = null,
    ): void {
        if (! $this->enabled()) {
            return;
        }

        $level = $this->resolveHttpLogLevel($status, $durationMs, $exception);
        $statusLabel = $status !== null ? (string) $status : 'error';

        $context = [
            'event' => 'remote_api.http',
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'duration_ms' => $durationMs,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($this->configuration !== null) {
            $context['module'] = $this->configuration->moduleName();
            $context['route'] = $this->configuration->routeName;
        }

        if ($requestTracker !== null) {
            $context['request_tracker'] = $requestTracker->toArray();
        }

        $context['rate_limiting'] = $this->rateLimitContext($url, $rateLimiter);

        if ($retryAfter !== null) {
            $context['retry_after'] = $retryAfter;
        }

        if ($exception !== null) {
            $context['exception'] = $exception->getMessage();
        }

        if ($durationMs >= $this->slowThresholdMs()) {
            $context['slow'] = true;
        }

        Log::channel($this->channel())->log(
            $level,
            sprintf('%s %s %s %dms', $method, $this->pathFromUrl($url), $statusLabel, $durationMs),
            $context,
        );
    }

    public function logCacheAccess(string $logicalKey, string $result, ?int $itemCount = null): void
    {
        if (! $this->enabled() || ! $this->shouldLogCacheAccess()) {
            return;
        }

        $context = [
            'event' => 'remote_api.cache',
            'logical_key' => $logicalKey,
            'result' => $result,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($this->configuration !== null) {
            $context['module'] = $this->configuration->moduleName();
            $context['route'] = $this->configuration->routeName;
        }

        if ($itemCount !== null) {
            $context['item_count'] = $itemCount;
        }

        Log::channel($this->channel())->info(
            sprintf('cache %s %s', $result, $logicalKey),
            $context,
        );
    }

    private function resolveHttpLogLevel(?int $status, int $durationMs, ?\Throwable $exception): string
    {
        if ($exception !== null || ($status !== null && $status >= 500)) {
            return 'error';
        }

        if ($status === 429 || ($status !== null && $status >= 400)) {
            return 'warning';
        }

        if ($durationMs >= $this->slowThresholdMs()) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * @return array<string, mixed>
     */
    private function rateLimitContext(string $url, ?RemoteApiRateLimiter $rateLimiter): array
    {
        $config = (array) config('modularous.remote_api.rate_limiting', []);

        $context = [
            'enabled' => (bool) ($config['enabled'] ?? true),
            'per_minute' => (int) ($config['per_minute'] ?? 60),
            'per_hour' => (int) ($config['per_hour'] ?? 1000),
        ];

        if ($rateLimiter !== null) {
            $context['endpoint_key'] = $this->endpointKeyFromUrl($url);
        }

        return $context;
    }

    private function endpointKeyFromUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $host = $parts['host'] ?? 'localhost';
        $path = rtrim($parts['path'] ?? '/', '/');

        if ($path !== '' && preg_match('#/\d+$#', $path) === 1) {
            $path = preg_replace('#/\d+$#', '', $path) ?? $path;
        }

        return sprintf('%s:%s', $host, $path === '' ? '/' : $path);
    }

    private function pathFromUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return $path . $query;
    }

    private function enabled(): bool
    {
        return (bool) config('modularous.remote_api.logging.enabled', true);
    }

    private function shouldLogCacheAccess(): bool
    {
        return (bool) config('modularous.remote_api.logging.log_cache', true);
    }

    private function channel(): string
    {
        return (string) config('modularous.remote_api.logging.channel', 'modularous-remote-api');
    }

    private function slowThresholdMs(): int
    {
        return max(0, (int) config('modularous.remote_api.logging.log_slow_requests_ms', 2000));
    }
}
