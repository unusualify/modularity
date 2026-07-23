<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache\Concerns;

use Unusualify\Modularous\Events\Cache\CacheWarmProgress;

trait BuildsCacheWarmBroadcastToast
{
    /**
     * Structured toast fields for CacheWarmProgress broadcasts.
     * Frontend displays these as-is; do not compose message text in JS.
     *
     * @return array{title: string, description: string, detail: ?string, variant: string}
     */
    protected function cacheWarmToast(
        string $status,
        string $title,
        string $description,
        ?string $detail = null,
        ?string $variant = null,
    ): array {
        return [
            'title' => $title,
            'description' => $description,
            'detail' => $detail,
            'variant' => $variant ?? match ($status) {
                CacheWarmProgress::STATUS_FAILED => 'error',
                CacheWarmProgress::STATUS_COMPLETED => 'success',
                CacheWarmProgress::STATUS_SKIPPED => 'warning',
                default => 'info',
            },
        ];
    }

    /**
     * Notation ModuleName:RouteName or ModuleName:RouteName:id when id is present.
     */
    protected function cacheWarmDetail(
        ?string $moduleName,
        ?string $moduleRouteName,
        mixed $id = null,
    ): ?string {
        if ($moduleName === null || $moduleName === '' || $moduleRouteName === null || $moduleRouteName === '') {
            return null;
        }

        $parts = [$moduleName, $moduleRouteName];

        if ($id !== null && $id !== '') {
            $parts[] = (string) $id;
        }

        return implode(':', $parts);
    }
}
