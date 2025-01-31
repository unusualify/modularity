<?php

namespace Unusualify\Modularity\Entities;

/**
 * No reverse relationship needed.
 * Repeater has one way access from the module it belongs to, (MorphTo).
 *
 * @author Hazarcan Doğa
 *
 * @version ${1:1.0.0}
 *
 * @since 08 Jan 2024
 *
 * @lastModifiedBy Hazarcan Doğa
 */
class Repeater extends Model
{
    protected $fillable = [
        'repatable_id',
        'content',
        'repeatable_type',
        'role',
        'locale',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function getTable()
    {
        return modularityConfig('tables.repeaters', parent::getTable());
    }
}
