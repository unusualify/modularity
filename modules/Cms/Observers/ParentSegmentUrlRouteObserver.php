<?php

namespace Modules\Cms\Observers;

use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\CmsParentSegmentResolver;
use WeakMap;

/**
 * Rebuilds {@see UrlRoute} PAGE_PUBLIC paths for affected models after parent-prefix rows change.
 *
 * @see CmsParentSegmentResolver
 * @see PublicUrlRegistryContract::syncPublicPageRoutesForAllModelsOfClass()
 */
final class ParentSegmentUrlRouteObserver
{
    /** @var list<string> Columns that alter resolved public prefixes or segment priority. */
    private const SYNC_KEYS = ['enabled', 'normalized_prefix', 'locale', 'target_model_class', 'sort_order'];

    /** @var WeakMap<ParentSegment, non-empty-string> */
    private static ?WeakMap $previousTargetsBySegment = null;

    public function __construct(
        private PublicUrlRegistryContract $registry,
    ) {}

    public function saving(ParentSegment $parentSegment): void
    {
        $this->stashPreviousTargetFqcnBeforeTargetChange($parentSegment);
    }

    public function created(ParentSegment $parentSegment): void
    {
        if (! $this->resyncEnabled()) {
            return;
        }

        foreach ($this->uniqueFqdns($this->currentTargetFqdns($parentSegment)) as $fqcn) {
            $this->registry->syncPublicPageRoutesForAllModelsOfClass($fqcn);
        }
    }

    public function updated(ParentSegment $parentSegment): void
    {
        if (! $this->resyncEnabled()) {
            return;
        }

        if (! $parentSegment->wasChanged(self::SYNC_KEYS)) {
            return;
        }

        foreach ($this->uniqueFqdns($this->updateResyncFqdns($parentSegment)) as $fqcn) {
            $this->registry->syncPublicPageRoutesForAllModelsOfClass($fqcn);
        }
    }

    public function deleted(ParentSegment $parentSegment): void
    {
        if (! $this->resyncEnabled()) {
            return;
        }

        $fqcn = trim((string) $parentSegment->target_model_class);
        if ($fqcn === '') {
            return;
        }

        $this->registry->syncPublicPageRoutesForAllModelsOfClass($fqcn);
    }

    private function stashPreviousTargetFqcnBeforeTargetChange(ParentSegment $parentSegment): void
    {
        $this->forgetRecordedPreviousTargetFor($parentSegment);

        if (! $this->resyncEnabled() || ! $parentSegment->exists || ! $parentSegment->isDirty('target_model_class')) {
            return;
        }

        $prev = trim((string) $parentSegment->getOriginal('target_model_class'));

        if ($prev !== '') {
            $this->previousTargetsWeakMap()->offsetSet($parentSegment, $prev);
        }
    }

    /** @param list<string> $fqdns */
    private function previousTargetsWeakMap(): WeakMap
    {
        return self::$previousTargetsBySegment ??= new WeakMap;
    }

    private function forgetRecordedPreviousTargetFor(ParentSegment $parentSegment): void
    {
        if (self::$previousTargetsBySegment === null) {
            return;
        }

        if ($this->previousTargetsWeakMap()->offsetExists($parentSegment)) {
            $this->previousTargetsWeakMap()->offsetUnset($parentSegment);
        }
    }

    /** @return list<string> */
    private function consumePreviousTargetFqdnOrEmpty(ParentSegment $parentSegment): array
    {
        if (
            self::$previousTargetsBySegment === null
            || ! $this->previousTargetsWeakMap()->offsetExists($parentSegment)
        ) {
            return [];
        }

        /** @var string $stored */
        $stored = $this->previousTargetsWeakMap()->offsetGet($parentSegment);
        $this->previousTargetsWeakMap()->offsetUnset($parentSegment);

        $stored = trim($stored);

        return $stored !== '' ? [$stored] : [];
    }

    /** @return list<string> */
    private function currentTargetFqdns(ParentSegment $parentSegment): array
    {
        $fqcn = trim((string) $parentSegment->target_model_class);

        return $fqcn !== '' ? [$fqcn] : [];
    }

    /** @return list<string> */
    private function updateResyncFqdns(ParentSegment $parentSegment): array
    {
        $current = trim((string) $parentSegment->target_model_class);
        /** @var list<string> */
        $targets = [];

        foreach ($this->consumePreviousTargetFqdnOrEmpty($parentSegment) as $prev) {
            $targets[] = $prev;
        }

        if ($current !== '') {
            $targets[] = $current;
        }

        return $targets;
    }

    /** @param list<string> $list */
    private function uniqueFqdns(array $list): array
    {
        $seen = [];
        foreach ($list as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $seen[$item] = true;
            }
        }

        return array_keys($seen);
    }

    private function resyncEnabled(): bool
    {
        return (bool) modularousConfig('cms_routing.resync_registry_after_parent_segments_change', true);
    }
}
