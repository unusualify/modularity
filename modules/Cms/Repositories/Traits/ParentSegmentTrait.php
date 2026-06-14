<?php

namespace Modules\Cms\Repositories\Traits;


/**
 * CMS: repositories whose {@see \Unusualify\Modularous\Repositories\Repository::getModel()} uses
 * {@see \Modules\Cms\Entities\Concerns\IsCmr} (content module route) and thus {@see HasParentSegment}.
 * Presentation shells ({@see \Modules\Cms\Entities\PageLayout}) are orthogonal; repositories also use {@see PageLayoutTrait} via {@see \Modules\Cms\Repositories\Traits\CmrTrait}.
 *
 * Enables parent-segment-aware tooling (hydrate selects, slug validation hooks) without hard-coding model classes.
 * Optional {@see $cmsAdminWarningsBuffer} / {@see pullCmsAdminWarnings()} support non-blocking panel hints after save
 * (e.g. {@see UrlRouteRegistrySyncTrait} stages via {@see setCmsAdminWarningsBuffer()}).
 */
trait ParentSegmentTrait
{
    /**
     * Staged non-blocking hints for the next panel JSON / redirect flash (path overlap, SEO soft checks, etc.).
     *
     * @var list<string>|null
     */
    protected ?array $cmsAdminWarningsBuffer = null;

    /**
     * Model FQCN managed by this repository.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public function parentSegmentTargetModelClass(): string
    {
        return get_class($this->getModel());
    }

    /**
     * Whether the bound model opts into parent-segment bindings / URL integration.
     */
    public function usesParentSegmentForUrl(): bool
    {
        return classHasTrait($this->parentSegmentTargetModelClass(), \Modules\Cms\Entities\Concerns\HasParentSegment::class);
    }

    /**
     * Consumes and clears any staged admin warnings from the last save (empty when none).
     *
     * @return list<string>
     */
    public function pullCmsAdminWarnings(): array
    {
        $warnings = $this->cmsAdminWarningsBuffer ?? [];
        $this->cmsAdminWarningsBuffer = null;

        return array_values($warnings);
    }

    /**
     * @param list<string> $warnings
     */
    protected function setCmsAdminWarningsBuffer(array $warnings): void
    {
        $this->cmsAdminWarningsBuffer = array_values($warnings);
    }
}
