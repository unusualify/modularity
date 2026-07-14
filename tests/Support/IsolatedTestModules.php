<?php

namespace Unusualify\Modularous\Tests\Support;

final class IsolatedTestModules
{
    private static ?string $path = null;

    public static function sourcePath(): string
    {
        return realpath(__DIR__ . '/../../test-modules') ?: __DIR__ . '/../../test-modules';
    }

    public static function testTokenSuffix(): string
    {
        if (defined('MODULAROUS_TEST_TOKEN')) {
            return (string) MODULAROUS_TEST_TOKEN;
        }

        return (string) (getenv('TEST_TOKEN') ?: getmypid());
    }

    public static function path(): string
    {
        self::sync();

        return self::$path ?? throw new \RuntimeException('Isolated test modules path was not initialized.');
    }

    public static function sync(): void
    {
        $destination = sys_get_temp_dir() . '/modularous_test_modules_' . self::testTokenSuffix();
        $marker = $destination . '/.source-fingerprint';
        $fingerprint = self::sourceFingerprint();

        if (
            is_dir($destination . '/TestModule')
            && is_file($marker)
            && hash_equals($fingerprint, (string) file_get_contents($marker))
        ) {
            self::$path = $destination;

            return;
        }

        self::removeDirectory($destination);
        self::copyDirectory(self::sourcePath(), $destination);
        file_put_contents($marker, $fingerprint);
        self::$path = $destination;
    }

    /**
     * @param array<string, array<string, bool>> $routesByModule
     */
    public static function seedRoutesStatuses(array $routesByModule = [
        'TestModule' => ['Item' => true],
        'SystemModule' => ['Item' => true],
    ]): void
    {
        foreach ($routesByModule as $module => $routes) {
            $file = self::path() . '/' . $module . '/routes_statuses.json';
            file_put_contents($file, json_encode($routes, JSON_PRETTY_PRINT));
        }
    }

    private static function sourceFingerprint(): string
    {
        $source = self::sourcePath();

        if (! is_dir($source)) {
            throw new \RuntimeException("Test modules source directory not found: {$source}");
        }

        $parts = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (! $item->isFile()) {
                continue;
            }

            $relativePath = $iterator->getSubPathName();
            $parts[] = $relativePath . ':' . $item->getSize() . ':' . $item->getMTime();
        }

        sort($parts);

        return hash('sha256', implode("\n", $parts));
    }

    private static function copyDirectory(string $source, string $destination): void
    {
        if (! is_dir($source)) {
            throw new \RuntimeException("Test modules source directory not found: {$source}");
        }

        if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
            throw new \RuntimeException("Unable to create isolated test modules directory: {$destination}");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

            if ($item->isDir()) {
                if (! is_dir($target) && ! mkdir($target, 0777, true) && ! is_dir($target)) {
                    throw new \RuntimeException("Unable to create directory: {$target}");
                }

                continue;
            }

            copy($item->getPathname(), $target);
        }
    }

    private static function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());

                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($directory);
    }
}
