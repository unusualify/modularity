<?php

namespace Unusualify\Modularity\Entities;

use Cartalyst\Tags\IlluminateTagged;

class Tagged extends IlluminateTagged
{
    public function getTable()
    {
        return unusualConfig('tables.tagged', parent::getTable());
    }
}
