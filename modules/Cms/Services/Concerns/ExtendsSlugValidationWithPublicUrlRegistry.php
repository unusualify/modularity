<?php

namespace Modules\Cms\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Unusualify\Modularous\Services\SlugInputValidationService;

/**
 * Extends {@see SlugInputValidationService} with optional checks against
 * {@see Modules\Cms\Contracts\PublicUrlRegistryContract}: nested path warnings + hard collision on exact locale/path.
 *
 * Subclasses override {@see slugModelsUsingPublicUrlRegistry()}, path building, and message/config hooks.
 */
trait ExtendsSlugValidationWithPublicUrlRegistry
{
    public function validateModelSlug(
        string $modelClass,
        string $value,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        $result = parent::validateModelSlug($modelClass, $value, $locale, $localeScoped, $excludeId);

        return $this->applyPublicUrlRegistryToSlugValidation($result, $modelClass, $value, $locale, $excludeId);
    }

    /**
     * @param array{valid: bool, message: ?string, normalized: string} $result
     * @return array{valid: bool, message: ?string, normalized: string, warnings: list<string>}
     */
    protected function applyPublicUrlRegistryToSlugValidation(
        array $result,
        string $modelClass,
        string $value,
        ?string $locale,
        ?int $excludeId,
    ): array {
        $nested = $this->nestedPublicUrlRegistryWarnings($modelClass, $value, $locale, $excludeId);
        $result['warnings'] = $nested;

        if (! ($result['valid'] ?? false)) {
            return $result;
        }

        if (! $this->slugModelUsesPublicUrlRegistry($modelClass)) {
            return $result;
        }

        if (! app()->bound(PublicUrlRegistryContract::class)) {
            return $result;
        }

        /** @var PublicUrlRegistryContract $registry */
        $registry = app(PublicUrlRegistryContract::class);

        if (! $registry->tableReady()) {
            return $result;
        }

        $localeStr = (string) ($locale ?? app()->getLocale());
        $path = $this->registryPathFromNormalizedSlug($modelClass, $result, $value, $localeStr);
        if ($path === null || $path === '') {
            return $result;
        }

        [$morphClass, $id] = $this->registryUrlableMorphForSlugValidation($modelClass, $excludeId);

        if ($registry->isPathClaimedByOther($localeStr, $path, $morphClass, $id)) {
            $result['valid'] = false;
            $result['message'] = $this->publicUrlRegistryPathCollisionMessage();
        }

        return $result;
    }

    /**
     * Whether this model's slug is registered in {@see PublicUrlRegistryContract}.
     */
    protected function slugModelUsesPublicUrlRegistry(string $modelClass): bool
    {
        return false;
    }

    /**
     * @return list<string>
     */
    protected function nestedPublicUrlRegistryWarnings(
        string $modelClass,
        string $value,
        ?string $locale,
        ?int $excludeId,
    ): array {
        if (! $this->slugModelUsesPublicUrlRegistry($modelClass)) {
            return [];
        }

        if (! $this->nestedPublicUrlRegistryWarningsEnabled()) {
            return [];
        }

        if (! app()->bound(PublicUrlRegistryContract::class)) {
            return [];
        }

        /** @var PublicUrlRegistryContract $registry */
        $registry = app(PublicUrlRegistryContract::class);

        if (! $registry->tableReady()) {
            return [];
        }

        $raw = trim($value);
        if ($raw === '') {
            return [];
        }

        $localeStr = (string) ($locale ?? app()->getLocale());
        $path = $this->nestedWarningPathFromRawSlug($modelClass, $raw, $localeStr);
        if ($path === null || $path === '') {
            return [];
        }

        $kind = $this->publicUrlRegistryRouteKindForNestedWarnings($modelClass);
        $morph = $this->publicUrlRegistryMorphClassForNestedWarnings($modelClass);
        if ($kind === null || $morph === null) {
            return [];
        }

        return $registry->nestedPathPrefixWarnings($localeStr, $path, $kind, $morph, $excludeId);
    }

    /**
     * Override when {@see nestedPublicUrlRegistryWarnings()} is used (e.g. modularous config flag).
     */
    protected function nestedPublicUrlRegistryWarningsEnabled(): bool
    {
        return true;
    }

    /**
     * Normalized path for collision check from parent validation result.
     *
     * @param array{valid: bool, message: ?string, normalized: string} $parentResult
     */
    protected function registryPathFromNormalizedSlug(
        string $modelClass,
        array $parentResult,
        string $value,
        string $locale,
    ): ?string {
        return '/' . ltrim((string) ($parentResult['normalized'] ?? ''), '/');
    }

    /**
     * Raw input path for nested warnings (may differ from stored slug normalization in edge cases).
     */
    protected function nestedWarningPathFromRawSlug(string $modelClass, string $rawValue, string $locale): ?string
    {
        return '/' . ltrim($rawValue, '/');
    }

    /**
     * @return array{0: class-string<Model>|null, 1: int|null}
     */
    protected function registryUrlableMorphForSlugValidation(string $modelClass, ?int $excludeId): array
    {
        return [$modelClass, $excludeId];
    }

    /**
     * @return non-empty-string|null
     */
    protected function publicUrlRegistryRouteKindForNestedWarnings(string $modelClass): ?string
    {
        return null;
    }

    /**
     * @return class-string<Model>|null
     */
    protected function publicUrlRegistryMorphClassForNestedWarnings(string $modelClass): ?string
    {
        return null;
    }

    protected function publicUrlRegistryPathCollisionMessage(): string
    {
        return __('This public URL path is already registered.');
    }
}
