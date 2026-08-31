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
     * Absolute path for a Blueprint class (may not exist yet).
     */
    public function path(Module $module, string $routeStudly, string $field): string
    {
        $def = BlueprintFieldCatalog::get($field);
        $folder = (string) modularousConfig('module_route_presentation.path', 'Blueprint');

        $dir = $module->getDirectoryPath(
            trim($folder, '/\\') . '/' . $routeStudly . '/' . $def['surface']
        );

        return $dir . '/' . $routeStudly . $def['class_suffix'] . '.php';
    }

    /**
     * @param array<string, mixed>|list<mixed>|string $items Evaluated array, or raw PHP array literal
     * @return string|null Absolute path written, or null if skipped
     */
    public function write(
        Module $module,
        string $routeStudly,
        string $field,
        array|string $items = [],
        bool $force = false,
    ): ?string {
        $def = BlueprintFieldCatalog::get($field);
        $folder = (string) modularousConfig('module_route_presentation.path', 'Blueprint');
        $folderNs = str_replace('/', '\\', trim($folder, '/\\'));

        $path = $this->path($module, $routeStudly, $field);
        $dir = dirname($path);

        if (! $this->filesystem->isDirectory($dir)) {
            $this->filesystem->makeDirectory($dir, 0755, true);
        }

        if ($this->filesystem->exists($path) && ! $force) {
            return null;
        }

        $class = $routeStudly . $def['class_suffix'];
        $namespace = $module->getBaseNamespace() . '\\' . $folderNs . '\\' . $routeStudly . '\\' . $def['surface'];

        $interface = $def['interface'];
        $interfaceShort = class_basename($interface);

        $itemsCode = is_string($items)
            ? BlueprintPhpArrayFormatter::indentForMethodReturn($items)
            : BlueprintPhpArrayFormatter::export($items);

        $replacements = [
            'NAMESPACE' => $namespace,
            'CLASS' => $class,
            'ITEMS' => $itemsCode,
            'INTERFACE' => $interface,
            'INTERFACE_SHORT' => $interfaceShort,
            'RETURN_DOC' => $def['return_doc'],
        ];

        $content = (string) new Stub('/' . $def['stub'] . '.stub', $replacements);
        $content = $this->injectExtraUses($content, $itemsCode);
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

    private function injectExtraUses(string $content, string $itemsCode): string
    {
        $extra = BlueprintPhpArrayFormatter::detectExtraUses($itemsCode);
        if ($extra === []) {
            return $content;
        }

        $block = implode("\n", $extra);

        $replaced = preg_replace(
            '/(use Unusualify\\\\Modularous\\\\ModuleRoute;)/',
            '$1' . "\n" . $block,
            $content,
            1
        );

        return is_string($replaced) ? $replaced : $content;
    }
}
