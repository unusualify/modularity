<?php

declare(strict_types=1);

namespace Modules\Cms\Services;

use Modules\SystemSetting\Services\SystemSettingsService;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Context-aware settings router.
 *
 * - Frontend (non-panel) requests → {@see CmsSettingsService}, falling back to {@see SystemSettingsService}
 * - Backend / panel / console → {@see SystemSettingsService}
 *
 * Force a layer with {@see forFrontend()} / {@see forBackend()} / {@see forCms()} / {@see forSystem()}.
 */
class SiteSettingsService
{
    private ?string $forcedContext = null;

    public function __construct(
        protected SystemSettingsService $systemSettings,
        protected CmsSettingsService $cmsSettings,
    ) {}

    /**
     * Force CMS layer with SystemSettings fallback (frontend semantics).
     */
    public function forFrontend(): static
    {
        $clone = clone $this;
        $clone->forcedContext = 'frontend';

        return $clone;
    }

    /**
     * Force SystemSettings only (backend semantics).
     */
    public function forBackend(): static
    {
        $clone = clone $this;
        $clone->forcedContext = 'backend';

        return $clone;
    }

    /**
     * Alias of {@see forFrontend()}.
     */
    public function forCms(): static
    {
        return $this->forFrontend();
    }

    /**
     * Alias of {@see forBackend()}.
     */
    public function forSystem(): static
    {
        return $this->forBackend();
    }

    public function usesCmsLayer(): bool
    {
        if ($this->forcedContext === 'frontend') {
            return true;
        }

        if ($this->forcedContext === 'backend') {
            return false;
        }

        if (app()->runningInConsole()) {
            return false;
        }

        try {
            return ! Modularous::isPanelUrl();
        } catch (\Throwable) {
            return true;
        }
    }

    public function get(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        if ($this->usesCmsLayer()) {
            if ($this->cmsSettings->filled($key, $locale)) {
                return $this->cmsSettings->get($key, null, $locale);
            }

            return $this->systemSettings->get($key, $default, $locale);
        }

        return $this->systemSettings->get($key, $default, $locale);
    }

    public function value(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        return $this->get($key, $default, $locale);
    }

    public function first(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        if ($this->usesCmsLayer()) {
            if ($this->cmsSettings->filled($key, $locale)) {
                return $this->cmsSettings->first($key, null, $locale);
            }

            return $this->systemSettings->first($key, $default, $locale);
        }

        return $this->systemSettings->first($key, $default, $locale);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(?string $locale = null): array
    {
        if (! $this->usesCmsLayer()) {
            return $this->systemSettings->all($locale);
        }

        return array_replace_recursive(
            $this->systemSettings->all($locale),
            $this->filterEmptyBranches($this->cmsSettings->all($locale)),
        );
    }

    public function has(string $key): bool
    {
        if ($this->usesCmsLayer()) {
            return $this->cmsSettings->has($key) || $this->systemSettings->has($key);
        }

        return $this->systemSettings->has($key);
    }

    public function filled(string $key, ?string $locale = null): bool
    {
        if ($this->usesCmsLayer()) {
            return $this->cmsSettings->filled($key, $locale) || $this->systemSettings->filled($key, $locale);
        }

        return $this->systemSettings->filled($key, $locale);
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->usesCmsLayer()) {
            $this->cmsSettings->set($key, $value);

            return;
        }

        $this->systemSettings->set($key, $value);
    }

    public function forgetCache(): void
    {
        $this->cmsSettings->forgetCache();
        $this->systemSettings->forgetCache();
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        if ($this->usesCmsLayer()) {
            return array_replace_recursive(
                $this->systemSettings->snapshot(),
                $this->filterEmptyBranches($this->cmsSettings->snapshot()),
            );
        }

        return $this->systemSettings->snapshot();
    }

    public function cms(): CmsSettingsService
    {
        return $this->cmsSettings;
    }

    public function system(): SystemSettingsService
    {
        return $this->systemSettings;
    }

    /**
     * Drop empty leaves so SystemSettings values remain visible under array_replace_recursive.
     *
     * @param  array<string, mixed>  $tree
     * @return array<string, mixed>
     */
    protected function filterEmptyBranches(array $tree): array
    {
        $filtered = [];

        foreach ($tree as $key => $value) {
            if (is_array($value)) {
                $nested = $this->filterEmptyBranches($value);
                if ($nested !== []) {
                    $filtered[$key] = $nested;
                }

                continue;
            }

            if ($value !== null && $value !== '') {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
