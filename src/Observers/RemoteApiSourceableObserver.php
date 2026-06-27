<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Observers;

use Illuminate\Database\Eloquent\Model;

class RemoteApiSourceableObserver
{
    public function retrieved(Model $model): void
    {
        if (! method_exists($model, 'hydrateRemoteApiAttributes')) {
            return;
        }

        $model->hydrateRemoteApiAttributes();
    }

    public function saving(Model $model): bool
    {
        if (method_exists($model, 'persistRemoteApiVirtualAttributes') && $model->exists) {
            $model->persistRemoteApiVirtualAttributes();
        }

        if (method_exists($model, 'stripRemoteApiVirtualAttributes') && $model->exists) {
            $model->stripRemoteApiVirtualAttributes();
        }

        return true;
    }

    public function saved(Model $model): void
    {
        if (method_exists($model, 'persistRemoteApiVirtualAttributes')) {
            $model->persistRemoteApiVirtualAttributes();
        }

        if (method_exists($model, 'stripRemoteApiVirtualAttributes')) {
            $model->stripRemoteApiVirtualAttributes();
        }
    }

    public function forceDeleting(Model $model): void
    {
        $model->remoteApiSource?->delete();
    }
}
