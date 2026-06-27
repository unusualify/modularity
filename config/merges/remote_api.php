<?php

declare(strict_types=1);

return [
    'base_url' => env('MODULAROUS_REMOTE_API_BASE_URL', ''),
    'token' => env('MODULAROUS_REMOTE_API_TOKEN'),
    'cache_ttl' => (int) env('MODULAROUS_REMOTE_API_CACHE_TTL', 3600),
    'timeout' => (int) env('MODULAROUS_REMOTE_API_TIMEOUT', 30),
    /*
     * Outgoing Remote API client rate limits. Defaults mirror modularous.api.rate_limiting
     * so sync stays within the same bounds as the remote API.
     */
    'rate_limiting' => [
        'enabled' => env('MODULAROUS_REMOTE_API_RATE_LIMITING_ENABLED', env('MODULAROUS_API_RATE_LIMITING_ENABLED', true)),
        'per_minute' => (int) env('MODULAROUS_REMOTE_API_RATE_LIMITING_PER_MINUTE', env('MODULAROUS_API_RATE_LIMITING_PER_MINUTE', 60)),
        'per_hour' => (int) env('MODULAROUS_REMOTE_API_RATE_LIMITING_PER_HOUR', env('MODULAROUS_API_RATE_LIMITING_PER_HOUR', 1000)),
    ],
    /*
     * Structured logging for outgoing Remote API HTTP requests (RemoteApiClient).
     * Channel is registered by Modularous as logging.channels.modularous-remote-api.
     */
    'logging' => [
        'enabled' => env('MODULAROUS_REMOTE_API_LOGGING_ENABLED', true),
        'channel' => env('MODULAROUS_REMOTE_API_LOGGING_CHANNEL', 'modularous-remote-api'),
        'log_slow_requests_ms' => (int) env('MODULAROUS_REMOTE_API_LOG_SLOW_MS', 2000),
        'log_cache' => env('MODULAROUS_REMOTE_API_LOG_CACHE', true),
    ],
];
