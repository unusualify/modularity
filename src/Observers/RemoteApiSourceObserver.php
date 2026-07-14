<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Observers;

use Unusualify\Modularous\Entities\RemoteApiSource;

class RemoteApiSourceObserver
{
    public function saved(RemoteApiSource $source): void
    {
        $sourceable = $source->sourceable;

        if ($sourceable !== null && $sourceable->exists) {
            $sourceable->touchQuietly();
        }
    }
}
