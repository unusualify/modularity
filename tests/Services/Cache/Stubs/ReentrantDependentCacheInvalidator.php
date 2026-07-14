<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache\Stubs;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Services\Cache\DependentCacheInvalidator;

class ReentrantDependentCacheInvalidator extends DependentCacheInvalidator
{
    public int $runCount = 0;

    protected function runInvalidation(Model $model): void
    {
        $this->runCount++;

        if ($this->runCount === 1) {
            $this->invalidateForModel($model);
        }
    }
}
