<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unusualify\Modularity\Entities\Repeater;
use Unusualify\Priceable\Traits\HasPriceable;

/**
 * @author Hazarcan Doğa
 * @version ${1:1.0.0}
 * @since 08 Jan 2024
 * @lastModifiedBy Hazarcan Doğa
 */
trait HasRepeaters
{
    use HasImages, HasFiles, HasPriceable;
    /**
     * Defines the one-to-many relationship between the module and Repeater.
     * Get all repeaters belonging to a module.
     * @uses  Unusualify\Modularity\Entities\Repeater::class
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function repeaters(): MorphMany
    {
        return $this->morphMany(
            Repeater::class,
            'repeatable',
        );
    }
}
