<?php

namespace Unusualify\Modularity\Entities;

use Cartalyst\Tags\IlluminateTag;

class Tag extends IlluminateTag
{
    protected static $taggedModel = Tagged::class;

    public function getTable()
    {
        return unusualConfig('tables.tags', parent::getTable());
    }
}
