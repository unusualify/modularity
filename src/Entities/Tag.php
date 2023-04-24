<?php

namespace OoBook\CRM\Base\Entities;

use Cartalyst\Tags\IlluminateTag;

class Tag extends IlluminateTag
{
    protected static $taggedModel = Tagged::class;

    public function getTable()
    {
        return config(getUnusualBaseKey() . '.tags_table', 'tags');
    }
}
