<?php

namespace Modules\Cms\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Entities\Concerns\HasPageLayout;
use Unusualify\Modularous\Repositories\Repository;

/**
 * CMS: repositories whose {@see Repository::getModel()} uses {@see HasPageLayout}.
 */
trait PageLayoutTrait
{
    /**
     * @return class-string<Model>
     */
    public function pageLayoutTargetModelClass(): string
    {
        return get_class($this->getModel());
    }

    public function usesPageLayoutPresentation(): bool
    {
        return classHasTrait($this->pageLayoutTargetModelClass(), HasPageLayout::class);
    }
}
