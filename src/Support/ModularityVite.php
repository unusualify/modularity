<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Foundation\Vite;

class ModularityVite extends Vite
{
    /**
     * The key to check for integrity hashes within the manifest.
     *
     * @var string|false
     */
    protected $integrityKey = 'integrity';

    /**
     * The configured entry points.
     *
     * @var array
     */
    protected $entryPoints = [];

    /**
     * The path to the "hot" file.
     *
     * @var string|null
     */
    protected $hotFile;

    /**
     * The path to the build directory.
     *
     * @var string
     */
    protected $buildDirectory = 'vendor/modularity';

    /**
     * The name of the manifest file.
     *
     * @var string
     */
    protected $manifestFilename = 'modularity-manifest.json';

}
