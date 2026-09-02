<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services;

use Unusualify\Modularous\Contracts\ModulePresentationAssetLoaderInterface;

final class ModulePresentationAssetLoader implements ModulePresentationAssetLoaderInterface
{
    private bool $defer = false;

    private bool $loaded = false;

    private ?\Closure $loadCallback = null;

    public function configure(bool $defer, \Closure $loadCallback): void
    {
        $this->defer = $defer;
        $this->loaded = ! $defer;
        $this->loadCallback = $loadCallback;
    }

    public function isDeferred(): bool
    {
        return $this->defer;
    }

    public function ensureLoaded(): void
    {
        if (! $this->defer || $this->loaded || $this->loadCallback === null) {
            return;
        }

        $this->loaded = true;

        ($this->loadCallback)();
    }
}
