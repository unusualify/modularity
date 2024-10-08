<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\HtmlString __invoke(string|string[] $entrypoints, string|null $buildDirectory = null)
 * @method static string hotAsset(string $asset)
 * @method static bool isRunningHot()
 * @method static string makeTagForChunk(string $src, string $url, array|null $chunk, array|null $manifest)
 * @method static string makePreloadTagForChunk(string $src, string $url, array $chunk, array $manifest)
 * @method static array chunk(array $manifest, string $file)
 * @method static array manifest(string $buildDirectory)
 * @method static string assetPath(string $path)
 * @method static bool isCssPath(string $path)
 *
 * @see \Unusualify\Modularity\Support\ModularityVite
 */
class ModularityVite extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Unusualify\Modularity\Support\ModularityVite::class;
    }
}
