<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Modularity\Entities\Asset;

trait HasAssets{

    public function assets(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(
            Asset::class,
            'assetable'
        );
    }


    /**
     *
     * @return Asset[]
     */

    public function getAssets()
    {
        return $this->assets()->get();
    }

    public function hasAsset($role = null)
    {
        return !!$role ?
                    $this->assets()->filter(function($asset) use ($role){
                        return $asset->role === $role;
                    })->count()
                    : $this->assets()->count();
    }










}
