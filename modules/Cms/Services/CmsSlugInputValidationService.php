<?php

namespace Modules\Cms\Services;

use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\Concerns\ExtendsSlugValidationWithPublicUrlRegistry;
use Unusualify\Modularous\Entities\Traits\HasParentSegment;
use Unusualify\Modularous\Services\SlugInputValidationService;

/**
 * CMS slug validation: core uniqueness + {@see PublicUrlRegistryContract} (path collision + nested path hints).
 *
 * Other modules: extend {@see SlugInputValidationService} and use {@see ExtendsSlugValidationWithPublicUrlRegistry},
 * binding their own {@see PublicUrlRegistryContract} implementation.
 */
class CmsSlugInputValidationService extends SlugInputValidationService
{
    use ExtendsSlugValidationWithPublicUrlRegistry {
        validateModelSlug as private validateModelSlugWithPublicUrlRegistry;
    }

    public function __construct(
        protected CmsParentSegmentResolver $parentSegmentResolver,
    ) {}

    /**
     * {@inheritdoc}
     *
     * Enforces optional {@see modularousConfig('cms_routing.admin.slug_max_path_segments')} before uniqueness/registry checks.
     */
    public function validateModelSlug(
        string $modelClass,
        string $value,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        $segmentFailure = $this->slugPathSegmentPolicyFailure($value);
        if ($segmentFailure !== null) {
            return [
                'valid' => false,
                'message' => $segmentFailure,
                'normalized' => '',
            ];
        }

        return $this->validateModelSlugWithPublicUrlRegistry($modelClass, $value, $locale, $localeScoped, $excludeId);
    }

    /**
     * When {@see modularousConfig('cms_routing.admin.slug_max_path_segments')} is set, reject raw values whose
     * slash-separated segment count exceeds the limit (admin path policy).
     */
    protected function slugPathSegmentPolicyFailure(string $rawValue): ?string
    {
        $max = modularousConfig('cms_routing.admin.slug_max_path_segments');
        if ($max === null || $max === '') {
            return null;
        }

        $max = (int) $max;
        if ($max < 1) {
            return null;
        }

        $trimmed = trim($rawValue);
        if ($trimmed === '') {
            return null;
        }

        $normalizedSlashes = str_replace('\\', '/', $trimmed);
        $segments = array_values(array_filter(explode('/', trim($normalizedSlashes, '/')), static fn ($s) => $s !== ''));

        if (count($segments) > $max) {
            return __('The slug may contain at most :max URL segment(s).', ['max' => $max]);
        }

        return null;
    }

    protected function slugModelUsesPublicUrlRegistry(string $modelClass): bool
    {
        return classHasTrait($modelClass, HasParentSegment::class);
    }

    protected function nestedPublicUrlRegistryWarningsEnabled(): bool
    {
        return (bool) modularousConfig('cms_routing.admin.slug_nested_path_warnings', true);
    }

    /**
     * @param array{valid: bool, message: ?string, normalized: string} $parentResult
     */
    protected function registryPathFromNormalizedSlug(
        string $modelClass,
        array $parentResult,
        string $value,
        string $locale,
    ): ?string {
        if (! classHasTrait($modelClass, HasParentSegment::class)) {
            return null;
        }

        return $this->parentSegmentResolver->joinPublicLeafPath(
            $modelClass,
            (string) $locale,
            (string) ($parentResult['normalized'] ?? '')
        );
    }

    protected function nestedWarningPathFromRawSlug(string $modelClass, string $rawValue, string $locale): ?string
    {
        if (! classHasTrait($modelClass, HasParentSegment::class)) {
            return null;
        }

        return $this->parentSegmentResolver->joinPublicLeafPath($modelClass, (string) $locale, ltrim($rawValue, '/'));
    }

    protected function publicUrlRegistryRouteKindForNestedWarnings(string $modelClass): ?string
    {
        if (! classHasTrait($modelClass, HasParentSegment::class)) {
            return null;
        }

        return UrlRoute::KIND_PAGE_PUBLIC;
    }

    protected function publicUrlRegistryMorphClassForNestedWarnings(string $modelClass): ?string
    {
        return classHasTrait($modelClass, HasParentSegment::class) ? $modelClass : null;
    }

    protected function publicUrlRegistryPathCollisionMessage(): string
    {
        return __('This public URL path is already registered for another page or redirect.');
    }
}
