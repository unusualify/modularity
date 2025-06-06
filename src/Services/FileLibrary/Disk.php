<?php

namespace Unusualify\Modularity\Services\FileLibrary;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as FilesystemManager;

class Disk implements FileServiceInterface
{
    /**
     * @var FilesystemManager
     */
    protected $filesystemManager;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(FilesystemManager $filesystemManager, Config $config)
    {
        $this->filesystemManager = $filesystemManager;
        $this->config = $config;
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function getUrl($id)
    {
        return $this->filesystemManager->disk($this->config->get(modularityBaseKey() . '.file_library.disk'))->url($id);
    }
}
