<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Modularity\Entities\Filepond;

trait HasFileponds
{

    public function fileponds(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(
            Filepond::class,
            'filepondable'
        );
    }

    /**
     *
     * @return Filepond[]
     */
    public function getFileponds()
    {
        return $this->fileponds()->get();
    }

    public function hasFilepond($role = null)
    {
        return !!$role
            ?   $this->fileponds()->filter(function($filepond) use ($role){
                    return $filepond->role === $role;
                })->count()
            :   $this->fileponds()->count();
    }
}
