<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Modularity\Entities\Filepond;

trait HasFileponds
{
    public function getFilepondableClass()
    {
        if(!$this->filepondableClass) {
            return $this;
        }

        $class = new $this->filepondableClass;

        $class->setAttribute($this->getKeyName(), $this->getKey());
        $class->fill($this->getAttributes());
        $class->setRelations($this->getRelations());

        return $class;
    }

    public function fileponds(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        $filepondableClass = $this->getFilepondableClass();

        return $filepondableClass->morphMany(
            Filepond::class,
            'filepondable'
        );
    }

    /**
     * @return Filepond[]
     */
    public function getFileponds()
    {
        return $this->fileponds()->get();
    }

    public function hasFilepond($role = null)
    {
        return (bool) $role
            ? $this->fileponds()->filter(function ($filepond) use ($role) {
                return $filepond->role === $role;
            })->count()
            : $this->fileponds()->count();
    }
}
