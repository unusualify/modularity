<?php

namespace OoBook\CRM\Base\Entities;

use Cartalyst\Tags\IlluminateTagged;

class Tagged extends IlluminateTagged
{
    public function getTable()
    {
        return config(getUnusualBaseKey() . '.tagged_table', 'tagged');
    }
}
