<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader as LaravelFileLoader;

class FileLoader extends LaravelFileLoader
{
    /**
     * Create a new file loader instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files, array|string $path)
    {
        parent::__construct($files, $path);
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getGroups(): array
    {
        $groups = [];

        foreach ($this->getPaths() as $dir) {
            foreach (glob($dir . '/**/*.php') as $path) {
                $group = basename($path, '.php');
                if (! in_array($group, $groups)) {
                    $groups[] = $group;
                }
            }
        }

        return $groups;
    }

    public function addPath(array|string $path)
    {
        $this->paths = array_merge($this->paths, is_string($path) ? [$path] : $path);
    }
}
