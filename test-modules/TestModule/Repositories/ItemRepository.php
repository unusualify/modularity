<?php

namespace TestModules\TestModule\Repositories;

use TestModules\TestModule\Entities\Item;
use Unusualify\Modularous\Repositories\Repository;

class ItemRepository extends Repository
{
    public function __construct(Item $model)
    {
        $this->model = $model;
    }

    /**
     * Minimal connector stub for dashboard metric widgets.
     */
    public function metricValue(): int
    {
        return (int) $this->model->newQuery()->count();
    }
}
