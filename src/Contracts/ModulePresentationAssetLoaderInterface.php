<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Contracts;

/**
 * Defers module view/Blade/translation registration on URL cache hits,
 * then loads them on demand when a live render is required.
 */
interface ModulePresentationAssetLoaderInterface
{
    /**
     * @param \Closure(): void $loadCallback Invoked once when deferred assets must be registered.
     */
    public function configure(bool $defer, \Closure $loadCallback): void;

    public function isDeferred(): bool;

    public function ensureLoaded(): void;
}
