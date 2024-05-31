<?php

namespace Unusualify\Modularity\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularity\Module;

class VueTestGenerator extends Generator
{
    /**
     * Vue Test Type
     *
     * @var string
     */
    public $type;

    /**
     * Defined Vue Test Types
     *
     * @var string
     */
    protected $types = [
        'component' => [
            'import_dir'        => 'components/',
            'target_dir'        => 'components',
            'file_convention'   => 'PascalCase',
            'stub'              => 'tests/vue-component',
            'extension'         => 'vue'
        ],
        'util' => [
            'import_dir'        => 'utils/',
            'target_dir'        => 'utils',
            'file_convention'   => 'CamelCase',
            'stub'              => 'tests/vue-util',
        ],
        'hook' => [
            'import_dir'        => 'hooks/',
            'target_dir'        => 'composables',
            'file_convention'   => 'CamelCase',
            'stub'              => 'tests/vue-composable',
        ],
        'store' => [
            'import_dir'        => 'store/modules/',
            'target_dir'        => 'store',
            'file_convention'   => 'KebabCase',
            'stub'              => 'tests/vue-store',

        ]
    ];

    /**
     * Stub Name
     *
     * @var string
     */
    protected $stub = 'tests/vue-test';

    /**
     * Sub Importing folder directory for js files
     *
     * @var string
     */
    public $subImportDir;

    /**
     * Sub target folder directory for js files
     *
     * @var mixed
     */
    public $subTargetDir;

    /**
     * The constructor.
     * @param $name
     * @param FileRepository $module
     * @param Config     $config
     * @param Filesystem $filesystem
     * @param Console    $console
     */
    public function __construct(
        $name,
        Config $config = null,
        Filesystem $filesystem = null,
        Console $console = null,
        Module $module = null
    ) {

        parent::__construct($name, $config, $filesystem, $console, $module);

    }

    /**
     * Set the type attribute
     *
     * @param string $fix
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type of test
     *
     * @return boolean|int
     */
    public function getType()
    {
        return $this->types[$this->type];
    }

    /**
     * Set the sub import directory attribute
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setSubImportDir($dir)
    {
        $this->subImportDir = $dir;

        return $this;
    }

    /**
     * Set the sub target directory attribute
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setSubTargetDir($dir)
    {
        $this->subTargetDir = $dir;

        return $this;
    }

    /**
     * Get defined test types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    public function getTypeImportDir()
    {
        $type = $this->getType();
        $sub = $this->subImportDir;
        $conventionMethod = 'get' . $type['file_convention'] ?? 'CamelCase';

        return $type['import_dir'] . ($sub ? $sub .'/' : '') . $this->{$conventionMethod}($this->name);
    }

    public function getTypeTargetDir()
    {
        $subFolder = $this->subTargetDir;

        return $this->getType()['target_dir'];
    }

    public function getTypeStubFile()
    {
        return $this->getType()['stub'];
    }

    /**
     * Get Stub File
     *
     * @param  string $name
     * @return string
     */
    public function getStubFile(string $name) : string
    {
        return $this->getFiles()[$name];
    }

    public function getTargetPath()
    {
        return base_path( unusualConfig('vendor_path') . '/vue/test' ) ;
    }

    public function getTestFileName()
    {
        // $file = $this->getStubFile($this->stub);
        $file = '$TEST_NAME$.test.js';

        $patterns = [
            '/\$TEST_NAME\$/' => $this->getKebabCase($this->name),
        ];

        return preg_replace( array_keys($patterns), array_values($patterns), $file);
    }

    /**
     * Generate the module.
     */
    public function generate() : int
    {
        $path = "{$this->getTargetPath()}/{$this->getTypeTargetDir()}/{$this->getTestFileName()}";

        // $content = (new Stub("/{$this->getTypeStubFile()}.stub",$this->getReplacement($this->stub)))->render();
        $content = (new Stub("/{$this->getTypeStubFile()}.stub", $this->makeReplaces([
            'STUDLY_NAME',
            'CAMEL_CASE',
            'NAMESPACE',
            'IMPORT',
        ])))->render();
        dd($content, $path);
        if (!$this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0775, true);
        }

        if(!file_exists($path)){
            $this->filesystem->put($path, $content);

            $this->console->info("Created : {$path} test file");
        }

        return 0;
    }

    public function getNamespaceReplacement()
    {
        $type = $this->getType();

        return "test/{$type['target_dir']}/{$this->getTestFileName()}";
    }

    public function getCamelCaseReplacement()
    {
        return $this->getCamelCase($this->getName());
    }

    public function getImportReplacement()
    {
        $type = $this->getType();
        $sub = $this->subImportDir;
        $conventionMethod = 'get' . $type['file_convention'] ?? 'CamelCase';

        $extension = isset($type['extension']) ? $type['extension'] : 'js';

        return $type['import_dir'] . ($sub ? $sub .'/' : '') . $this->{$conventionMethod}($this->name) . '.' . $extension;
    }
}
