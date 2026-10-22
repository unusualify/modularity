<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularous\Services\CoverageService;

/**
 * Coverage Analysis Facade
 *
 * @method static \Unusualify\Modularous\Services\CoverageService make(?string $cloverDir = null, ?string $cloverName = null)
 * @method static \Unusualify\Modularous\Services\CoverageService setCloverPath(string $path)
 * @method static \Unusualify\Modularous\Services\CoverageService filterByFiles(array $files)
 * @method static \Unusualify\Modularous\Services\CoverageService setCoverageThreshold(float $threshold)
 * @method static \Unusualify\Modularous\Services\CoverageService skipMagicMethods(bool $skip = true)
 * @method static \Unusualify\Modularous\Services\CoverageService skipPrivateMethods(bool $skip = true)
 * @method static \Unusualify\Modularous\Services\CoverageService skipProtectedMethods(bool $skip = true)
 * @method static array analyze()
 * @method static array analyzeFile(string $filePath)
 * @method static array|null getMethodCoverage(string $filePath, string $methodName)
 * @method static array getErrors()
 * @method static bool hasErrors()
 * @method static array git(string $baseBranch = 'main')
 * @method static array uncovered(array $files = [])
 * @method static array partial(float $threshold = 50.0, array $files = [])
 * @method static string json(?array $files = null, bool $prettyPrint = true)
 * @method static bool save(string $outputPath, ?array $files = null, string $format = 'json')
 * @method static string markdown(?array $files = null)
 * @method static string html(?array $files = null)
 * @method static bool checkPR(string $baseBranch = 'main', bool $throwOnFailure = false)
 * @method static array stats(?array $files = null)
 *
 * @see CoverageService
 */
class Coverage extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'coverage.service';
    }
}
