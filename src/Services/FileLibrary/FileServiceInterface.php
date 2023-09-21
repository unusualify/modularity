<?php

namespace OoBook\CRM\Base\Services\FileLibrary;

interface FileServiceInterface
{
    /**
     * @param string $id
     * @return string
     */
    public function getUrl($id);
}
