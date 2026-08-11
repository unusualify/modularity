<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint;

use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularous\Module;

/**
 * Writes a single Blueprint provider class from stubs.
 */
final class BlueprintClassWriter
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
        Stub::setBasePath(dirname(__DIR__) . '/stubs');
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $items
     * @return string|null Absolute path written, or null if skipped
     */
    public function write(
        Module $module,
        string $routeStudly,
        string $field,
        array $items = [],
        bool $force = false,
    ): ?string {
        $def = BlueprintFieldCatalog::get($field);
        $folder = (string) modularousConfig('module_route_presentation.path', 'Blueprint');
        $folderNs = str_replace('/', '\\', trim($folder, '/\\'));

        $dir = $module->getDirectoryPath(
            trim($folder, '/\\') . '/' . $routeStudly . '/' . $def['surface']
        );

        if (! $this->filesystem->isDirectory($dir)) {
            $this->filesystem->makeDirectory($dir, 0755, true);
        }

        $class = $routeStudly . $def['class_suffix'];
        $namespace = $module->getBaseNamespace() . '\\' . $folderNs . '\\' . $routeStudly . '\\' . $def['surface'];
        $path = $dir . '/' . $class . '.php';

        if ($this->filesystem->exists($path) && ! $force) {
            return null;
        }

        $interface = $def['interface'];
        $interfaceShort = class_basename($interface);

        $replacements = [
            'NAMESPACE' => $namespace,
            'CLASS' => $class,
            'ITEMS' => var_export($items, true),
            'INTERFACE' => $interface,
            'INTERFACE_SHORT' => $interfaceShort,
            'RETURN_DOC' => $def['return_doc'],
        ];

        $content = (string) new Stub('/' . $def['stub'] . '.stub', $replacements);
        $this->filesystem->put($path, $content);

        return $path;
    }

    public function fqcn(Module $module, string $routeStudly, string $field): string
    {
        $def = BlueprintFieldCatalog::get($field);
        $folder = (string) modularousConfig('module_route_presentation.path', 'Blueprint');
        $folderNs = str_replace('/', '\\', trim($folder, '/\\'));

        return $module->getBaseNamespace()
            . '\\' . $folderNs
            . '\\' . $routeStudly
            . '\\' . $def['surface']
            . '\\' . $routeStudly . $def['class_suffix'];
    }
}
