<?php

namespace Modules\Cms\Repositories\Traits;

/**
 * CMS: repositories whose {@see \Unusualify\Modularous\Repositories\Repository::getModel()} uses {@see \Modules\Cms\Entities\Concerns\HasPageLayout}.
 */
trait PageLayoutTrait
{
    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public function pageLayoutTargetModelClass(): string
    {
        return get_class($this->getModel());
    }

    public function usesPageLayoutPresentation(): bool
    {
        return classHasTrait($this->pageLayoutTargetModelClass(), \Modules\Cms\Entities\Concerns\HasPageLayout::class);
    }
}
