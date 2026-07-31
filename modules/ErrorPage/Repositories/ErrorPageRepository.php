<?php

declare(strict_types=1);

namespace Modules\ErrorPage\Repositories;

use Modules\Cms\Repositories\Traits\PageLayoutTrait;
use Modules\ErrorPage\Entities\ErrorPage;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\PublishableTrait;

class ErrorPageRepository extends Repository
{
    use PageLayoutTrait,
        PublishableTrait;

    public function __construct(ErrorPage $model)
    {
        $this->model = $model;
    }
}
