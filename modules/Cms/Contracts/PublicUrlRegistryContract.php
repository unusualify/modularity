<?php

namespace Modules\Cms\Contracts;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Providers\CmsServiceProvider;
use Modules\Cms\Services\CmsUrlRouteRegistry;

/**
 * CMS public URL registry: locale + normalized path rows owned by morph urlables.
 * Implemented by {@see CmsUrlRouteRegistry}; bind in {@see CmsServiceProvider}.
 */
interface PublicUrlRegistryContract
{
    public function tableReady(): bool;

    /**
     * Rebuild PAGE_PUBLIC UrlRoute rows for every model instance of {@code $modelClass}
     * (HasSlug / IsSingular targets — see registry implementation).
     *
     * @param class-string<Model> $modelClass
     */
    public function syncPublicPageRoutesForAllModelsOfClass(string $modelClass): void;

    /**
     * Whether another row already claims this locale + path.
     *
     * @param class-string<Model>|null $excludeUrlableType Morph class of the entity being edited (exclude that pair)
     * @param int|null $excludeUrlableId Primary key paired with {@see $excludeUrlableType}
     */
    public function isPathClaimedByOther(string $locale, string $normalizedPath, ?string $excludeUrlableType = null, ?int $excludeUrlableId = null): bool;

    /**
     * Non-blocking hints when two public paths share a prefix/child relationship (same route kind + owner morph).
     *
     * @param class-string<Model> $pathOwnerMorphClass
     * @return list<string>
     */
    public function nestedPathPrefixWarnings(string $locale, string $normalizedPath, string $routeKind, string $pathOwnerMorphClass, ?int $exceptUrlableId = null): array;
}
