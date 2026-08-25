<?php

namespace Modules\Cms\Repositories;

use Modules\Cms\Entities\HomepageTest;
use Modules\Cms\Repositories\Traits\CmrTrait;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;
use Unusualify\Modularous\Repositories\Traits\PublishableTrait;
use Unusualify\Modularous\Repositories\Traits\RevisionsTrait;
use Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait;

class HomepageTestRepository extends Repository
{
    use FilepondsTrait,
        ImagesTrait,
        CmrTrait,
        TranslatableMetadataTrait,
        PublishableTrait,
        RevisionsTrait;

    public function __construct(HomepageTest $model)
    {
        $this->model = $model;
    }
}
