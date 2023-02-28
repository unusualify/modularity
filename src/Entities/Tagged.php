<?php

namespace OoBook\CRM\Base\Entities;

use Cartalyst\Tags\IlluminateTagged;

class Tagged extends IlluminateTagged
{
    public function getTable()
    {
        return config('base.tagged_table', 'tagged');
    }
}
