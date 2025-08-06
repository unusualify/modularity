<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Unusualify\Modularity\Facades\Modularity;

trait ApiRateLimiting
{
    /**
     * API rate limit per minute
     *
     * @var int
     */
    protected $rateLimit = 60;

    /**
     * API rate limit per hour
     *
     * @var int
     */
    protected $rateLimitPerHour = 1000;

    /**
     * API rate limit blocking time
     *
     * @var int
     */
    protected $rateLimitBlockingTime = 3600;

    /**
     * API rate limit blocking maximum attempts
     *
     * @var int
     */
    protected $rateLimitBlockingMaximumAttempts = 250;

    /**
     * API rate limit blocking time threshold
     *
     * @var int
     */
    protected $rateLimitBlockingTimeThreshold = 300;

    /**
     * Check if rate limiting is enabled
     */
    protected function isRateLimitingEnabled(): bool
    {
        return modularityConfig('api.rate_limiting.enabled', true);
    }

    /**
     * Get rate limit per minute
     */
    protected function getRateLimit(): int
    {
        return modularityConfig('api.rate_limiting.per_minute', $this->rateLimit);
    }

    /**
     * Get rate limit per hour
     */
    protected function getRateLimitPerHour(): int
    {
        return modularityConfig('api.rate_limiting.per_hour', $this->rateLimitPerHour);
    }

    /**
     * Get rate limit blocking time
     */
    protected function getRateLimitBlockingTime(): int
    {
        return modularityConfig('api.rate_limiting.blocking_time', $this->rateLimitBlockingTime);
    }

    /**
     * Get rate limit blocking maximum attempts
     */
    protected function getBlockingMaximumAttempts(): int
    {
        return modularityConfig('api.rate_limiting.blocking_maximum_attempts', $this->rateLimitBlockingMaximumAttempts);
    }

    /**
     * Get rate limit blocking time threshold
     */
    protected function getBlockingTimeThreshold(): int
    {
        return modularityConfig('api.rate_limiting.blocking_time_threshold', $this->rateLimitBlockingTimeThreshold);
    }

    /**
     * Get rate limit key for current request
     */
    protected function getRateLimitKey(string $type = 'minute'): string
    {
        $user = $this->getApiUser();
        $identifier = $user ? $user->id : $this->request->ip();

        return "api_rate_limit_{$type}:{$identifier}";
    }

    /**
     * Check if user is blocked
     */
    protected function isUserBlocked(): bool
    {
        if (! $this->isRateLimitingEnabled()) {
            return false;
        }

        $blockKey = $this->getRateLimitKey('blocked');

        return RateLimiter::tooManyAttempts($blockKey, 1);
    }

    /**
     * Block user for configured blocking time
     */
    protected function blockUser(): void
    {
        if (! $this->isRateLimitingEnabled()) {
            return;
        }

        $blockKey = $this->getRateLimitKey('blocked');
        RateLimiter::hit($blockKey, $this->getRateLimitBlockingTime());
    }

    /**
     * Get remaining block time
     */
    protected function getRemainingBlockTime(): int
    {
        if (! $this->isRateLimitingEnabled()) {
            return 0;
        }

        $blockKey = $this->getRateLimitKey('blocked');

        return RateLimiter::availableIn($blockKey);
    }

    /**
     * Check if request is rate limited
     */
    protected function isRateLimited(): bool
    {
        if (! $this->isRateLimitingEnabled()) {
            return false;
        }

        // First check if user is blocked
        if ($this->isUserBlocked()) {
            return true;
        }

        $minuteKey = $this->getRateLimitKey('minute');
        $hourKey = $this->getRateLimitKey('hour');
        $blockingKey = $this->getRateLimitKey('blocking_threshold');

        // Check if blocking threshold is exceeded
        if (RateLimiter::tooManyAttempts($blockingKey, $this->getBlockingMaximumAttempts())) {
            $this->blockUser();

            return true;
        }

        return RateLimiter::tooManyAttempts($minuteKey, $this->getRateLimit()) ||
               RateLimiter::tooManyAttempts($hourKey, $this->getRateLimitPerHour());
    }

    /**
     * Apply rate limiting to request
     */
    protected function applyRateLimit(): ?JsonResponse
    {
        // get request origin
        $host = $this->request->header('host');

        if(str_contains($host, Modularity::getAppHost()) || str_contains($host, Modularity::getAdminAppHost())){
            return null;
        }

        if (! $this->isRateLimitingEnabled()) {
            return null;
        }

        // Check if user is blocked
        if ($this->isUserBlocked()) {
            $remainingTime = $this->getRemainingBlockTime();
            $minutes = ceil($remainingTime / 60);

            return $this->respondWithError(
                "You have been blocked for exceeding the rate limit. Please try again in {$minutes} minutes.",
                429,
                [
                    'X-RateLimit-Blocked' => true,
                    'X-RateLimit-Block-Remaining' => $remainingTime,
                    'X-RateLimit-Block-Reset' => now()->addSeconds($remainingTime)->timestamp,
                ]
            );
        }

        if ($this->isRateLimited()) {
            return $this->respondWithError('Too many requests', 429);
        }

        $minuteKey = $this->getRateLimitKey('minute');
        $hourKey = $this->getRateLimitKey('hour');
        $blockingKey = $this->getRateLimitKey('blocking_threshold');

        RateLimiter::hit($minuteKey, 60); // 1 minute
        RateLimiter::hit($hourKey, 3600); // 1 hour
        RateLimiter::hit($blockingKey, $this->getBlockingTimeThreshold()); // configurable threshold

        return null;
    }

    /**
     * Get remaining rate limit attempts
     */
    protected function getRemainingAttempts(): int
    {
        if (! $this->isRateLimitingEnabled()) {
            return $this->getRateLimit();
        }

        $minuteKey = $this->getRateLimitKey('minute');
        $hourKey = $this->getRateLimitKey('hour');
        $blockingKey = $this->getRateLimitKey('blocking_threshold');

        return min(
            RateLimiter::retriesLeft($minuteKey, $this->getRateLimit()),
            RateLimiter::retriesLeft($hourKey, $this->getRateLimitPerHour()),
            RateLimiter::retriesLeft($blockingKey, $this->getBlockingMaximumAttempts())
        );
    }

    /**
     * Get rate limit reset time
     */
    protected function getRateLimitResetTime(): int
    {
        if (! $this->isRateLimitingEnabled()) {
            return 0;
        }

        $minuteKey = $this->getRateLimitKey('minute');
        $hourKey = $this->getRateLimitKey('hour');
        $blockingKey = $this->getRateLimitKey('blocking_threshold');

        return min(
            RateLimiter::availableIn($minuteKey),
            RateLimiter::availableIn($hourKey),
            RateLimiter::availableIn($blockingKey)
        );
    }

    /**
     * Get rate limit headers
     */
    protected function getRateLimitHeaders(): array
    {
        if (! $this->isRateLimitingEnabled()) {
            return [];
        }

        $headers = [
            'X-RateLimit-Limit' => $this->getRateLimit(),
            'X-RateLimit-Remaining' => $this->getRemainingAttempts(),
            'X-RateLimit-Reset' => $this->getRateLimitResetTime(),
            'X-RateLimit-Hourly-Limit' => $this->getRateLimitPerHour(),
            'X-RateLimit-Hourly-Remaining' => RateLimiter::retriesLeft($this->getRateLimitKey('hour'), $this->getRateLimitPerHour()),
            'X-RateLimit-Blocking-Limit' => $this->getBlockingMaximumAttempts(),
            'X-RateLimit-Blocking-Remaining' => RateLimiter::retriesLeft($this->getRateLimitKey('blocking_threshold'), $this->getBlockingMaximumAttempts()),
            'X-RateLimit-Blocking-Threshold' => $this->getBlockingTimeThreshold(),
        ];

        // Add blocking information if user is blocked
        if ($this->isUserBlocked()) {
            $headers['X-RateLimit-Blocked'] = true;
            $headers['X-RateLimit-Block-Remaining'] = $this->getRemainingBlockTime();
            $headers['X-RateLimit-Block-Reset'] = now()->addSeconds($this->getRemainingBlockTime())->timestamp;
        }

        return $headers;
    }
}
