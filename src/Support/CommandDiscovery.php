<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Support;

use Illuminate\Console\Command;

/**
 * Discovers console command classes from given paths by extracting FQCN from file contents.
 * Excludes abstract classes, interfaces, enums, and traits. Verifies classes extend Command.
 */
class CommandDiscovery
{
    /**
     * Discover command FQCNs from the given glob paths.
     *
     * Excludes:
     * - Abstract classes
     * - Interfaces
     * - Enums
     * - Traits
     * - Classes that do not extend \Illuminate\Console\Command
     *
     * @param array<string> $paths Glob patterns (e.g. __DIR__ . '/../Console/*.php')
     * @param array<string> $exclude Optional class names to exclude (e.g. for legacy compatibility)
     * @return array<string> Fully qualified class names
     */
    public static function discover(array $paths, array $exclude = []): array
    {
        $commands = [];

        foreach ($paths as $path) {
            $files = glob($path);

            if ($files === false) {
                continue;
            }

            foreach ($files as $filePath) {
                $filePath = realpath($filePath);

                if ($filePath === false || ! is_file($filePath)) {
                    continue;
                }

                $className = basename($filePath, '.php');

                if (in_array($className, $exclude, true)) {
                    continue;
                }

                $fileContents = file_get_contents($filePath);

                if ($fileContents === false) {
                    continue;
                }

                if (self::isNonCommandDeclaration($fileContents)) {
                    continue;
                }

                if (! preg_match('/namespace\s+([^;]+);/', $fileContents, $matches)) {
                    continue;
                }

                $namespace = trim($matches[1]);
                $fqcn = $namespace . '\\' . $className;

                if (! self::isLoadableCommand($fqcn)) {
                    continue;
                }

                $commands[] = $fqcn;
            }
        }

        return array_values(array_unique($commands));
    }

    /**
     * Skip files that declare abstract classes, interfaces, enums, or traits.
     */
    private static function isNonCommandDeclaration(string $fileContents): bool
    {
        $fileContents = self::stripCommentsAndStrings($fileContents);

        return preg_match('/\babstract\s+class\b/', $fileContents) === 1
            || preg_match('/\binterface\s+\w+/', $fileContents) === 1
            || preg_match('/\benum\s+\w+/', $fileContents) === 1
            || preg_match('/\btrait\s+\w+/', $fileContents) === 1;
    }

    /**
     * Strip PHP comments and strings to avoid false positives in declarations.
     */
    private static function stripCommentsAndStrings(string $source): string
    {
        $result = preg_replace('/\/\*.*?\*\//s', '', $source);
        $result = preg_replace('/\/\/[^\n]*/', '', $result ?? '');
        $result = preg_replace('/# [^\n]*/', '', $result ?? '');
        $result = preg_replace('/\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'/', "''", $result ?? '');
        $result = preg_replace('/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/', '""', $result ?? '');

        return $result ?? '';
    }

    /**
     * Verify the class exists, is instantiable, and extends Command.
     */
    private static function isLoadableCommand(string $fqcn): bool
    {
        if (! class_exists($fqcn)) {
            return false;
        }

        try {
            $reflection = new \ReflectionClass($fqcn);

            return $reflection->isInstantiable()
                && $reflection->isSubclassOf(Command::class);
        } catch (\Throwable) {
            return false;
        }
    }
}
