<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Illuminate\Support\Facades\RateLimiter;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;

class RemoteApiRateLimiter
{
    /**
     * @param array<string, mixed>|null $config
     */
    public function __construct(
        private readonly ?array $config = null,
    ) {}

    public function assertCanRequest(string $url): void
    {
        $config = $this->config();

        if (! ($config['enabled'] ?? true)) {
            return;
        }

        $baseKey = $this->resolveKey($url);

        $minuteLimit = max(1, (int) ($config['per_minute'] ?? 60));
        $minuteKey = $baseKey . ':minute';

        if (RateLimiter::tooManyAttempts($minuteKey, $minuteLimit)) {
            throw RemoteApiSyncException::rateLimitExceeded(
                $url,
                RateLimiter::availableIn($minuteKey),
                'minute',
                $minuteLimit,
            );
        }

        $hourLimit = max(1, (int) ($config['per_hour'] ?? 1000));
        $hourKey = $baseKey . ':hour';

        if (RateLimiter::tooManyAttempts($hourKey, $hourLimit)) {
            throw RemoteApiSyncException::rateLimitExceeded(
                $url,
                RateLimiter::availableIn($hourKey),
                'hour',
                $hourLimit,
            );
        }
    }

    public function hit(string $url): void
    {
        $config = $this->config();

        if (! ($config['enabled'] ?? true)) {
            return;
        }

        $baseKey = $this->resolveKey($url);

        RateLimiter::hit($baseKey . ':minute', 60);
        RateLimiter::hit($baseKey . ':hour', 3600);
    }

    /**
     * @return array<string, mixed>
     */
    private function config(): array
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $remoteApiConfig = (array) config('modularous.remote_api.rate_limiting', []);

        if ($remoteApiConfig !== []) {
            return $remoteApiConfig;
        }

        return (array) config('modularous.api.rate_limiting', []);
    }

    private function resolveKey(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return 'remote-api-outgoing:' . md5($url);
        }

        $host = $parts['host'] ?? 'localhost';
        $path = rtrim($parts['path'] ?? '/', '/');

        if ($path !== '' && preg_match('#/\d+$#', $path) === 1) {
            $path = preg_replace('#/\d+$#', '', $path) ?? $path;
        }

        return sprintf('remote-api-outgoing:%s:%s', $host, $path === '' ? '/' : $path);
    }
}
