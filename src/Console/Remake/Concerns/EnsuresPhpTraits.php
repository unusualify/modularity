<?php

namespace Unusualify\Modularous\Console\Remake\Concerns;

use Illuminate\Support\Facades\File;
use ReflectionClass;

/**
 * Shared helpers for remake commands that inject PHP trait imports / class-level use entries.
 */
trait EnsuresPhpTraits
{
    /**
     * @param  class-string  $class
     * @param  class-string  $trait
     */
    protected function ensureTraitOnClass(string $class, string $trait, bool $dryRun): bool
    {
        if (classHasTrait($class, $trait)) {
            $this->line('  · ' . $class . ' already uses ' . class_basename($trait));

            return false;
        }

        $path = (new ReflectionClass($class))->getFileName();
        if ($path === false || ! is_readable($path)) {
            $this->warn("  · Cannot read source for {$class}");

            return false;
        }

        $contents = File::get($path);
        $updated = $this->insertTraitIntoPhpSource($contents, $trait);
        if ($updated === $contents) {
            $this->warn('  · Could not automatically add ' . class_basename($trait) . " to {$class}");

            return false;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . 'Add ' . class_basename($trait) . " → {$class}");

        if (! $dryRun) {
            File::put($path, $updated);
        }

        return true;
    }

    /**
     * Insert a trait import + class-level use entry into PHP source.
     */
    protected function insertTraitIntoPhpSource(string $contents, string $traitFqcn): string
    {
        $short = class_basename($traitFqcn);

        if (! preg_match('/\buse\s+' . preg_quote($traitFqcn, '/') . '\s*;/', $contents)) {
            if (preg_match('/^namespace\s+[^;]+;\s*\n/m', $contents, $m, PREG_OFFSET_CAPTURE)) {
                $insertAt = $m[0][1] + strlen($m[0][0]);
                $contents = substr($contents, 0, $insertAt)
                    . "\nuse {$traitFqcn};\n"
                    . substr($contents, $insertAt);
            }
        }

        if (! preg_match('/class\s+\w+[^{]*\{/', $contents, $classMatch, PREG_OFFSET_CAPTURE)) {
            return $contents;
        }

        $classBodyStart = $classMatch[0][1] + strlen($classMatch[0][0]);
        $slice = substr($contents, $classBodyStart);

        if (preg_match('/^(\s*)use\s+([^;]+);/m', $slice, $inner, PREG_OFFSET_CAPTURE)) {
            $traitsList = $inner[2][0];
            if (preg_match('/\b' . preg_quote($short, '/') . '\b/', $traitsList)) {
                return $contents;
            }

            $newList = trim($traitsList);
            $newList = $short . ($newList !== '' ? ', ' . $newList : '');
            $replacement = $inner[1][0] . 'use ' . $newList . ';';
            $absolute = $classBodyStart + $inner[0][1];

            return substr($contents, 0, $absolute)
                . $replacement
                . substr($contents, $absolute + strlen($inner[0][0]));
        }

        return substr($contents, 0, $classBodyStart)
            . "\n    use {$short};\n"
            . substr($contents, $classBodyStart);
    }

    /**
     * Ensure a `use Fqcn;` import exists after the namespace declaration.
     */
    protected function ensurePhpImport(string $contents, string $fqcn): string
    {
        if (preg_match('/\buse\s+' . preg_quote($fqcn, '/') . '\s*;/', $contents)) {
            return $contents;
        }

        if (! preg_match('/^namespace\s+[^;]+;\s*\n/m', $contents, $m, PREG_OFFSET_CAPTURE)) {
            return $contents;
        }

        $insertAt = $m[0][1] + strlen($m[0][0]);

        return substr($contents, 0, $insertAt)
            . "\nuse {$fqcn};\n"
            . substr($contents, $insertAt);
    }
}
