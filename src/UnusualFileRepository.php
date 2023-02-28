<?php

namespace OoBook\CRM\Base;

use Nwidart\Modules\FileRepository;

class UnusualFileRepository extends FileRepository
{
    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        // dd($args[2]);
        return new \OoBook\CRM\Base\Module(...$args);
    }


}
