<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Jobs\ReorderNestedModuleItems;

trait NestingTrait
{
    /**
     * The Laravel queue name to be used for the reordering operation.
     *
     * @var string
     */
    protected $reorderNestedModuleItemsJobQueue = 'default';

    /**
     * @param string $nestedSlug
     * @param array $with
     * @param array $withCount
     * @param array $scopes
     * @return \Unusualify\Modularity\Entities\Model|null
     */
    public function forNestedSlug($nestedSlug, $with = [], $withCount = [], $scopes = [])
    {
        $targetSlug = collect(explode('/', $nestedSlug))->last();

        $targetItem = $this->forSlug($targetSlug, $with, $withCount, $scopes);

        if (! $targetItem || $nestedSlug !== $targetItem->nestedSlug) {
            return null;
        }

        return $targetItem;
    }

    public function setNewOrder($ids)
    {
        ReorderNestedModuleItems::dispatch($this->model, $ids)
            ->onQueue($this->reorderNestedModuleItemsJobQueue);
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @return void
     */
    public function afterRestore($object)
    {
        if (! $object->parent) {
            $object->parent_id = null;
            $object->save();
        }
    }
}
