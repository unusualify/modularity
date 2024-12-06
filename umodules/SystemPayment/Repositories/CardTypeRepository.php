<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\CardType;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;

class CardTypeRepository extends Repository
{
    use ImagesTrait;

    public function __construct(CardType $model)
    {
        $this->model = $model;
    }
}
