<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Entities\Revisions;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Unusualify\Modularous\Entities\Revision;
use Unusualify\Modularous\Entities\Singleton;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Shared revision rows for all {@see \Unusualify\Modularous\Entities\Traits\IsSingular} models
 * (parent table {@see Modularous::config} keys {@code tables.singletons} / {@code tables.singleton_revisions}).
 */
class SingletonRevision extends Revision
{
    protected $fillable = [
        'singleton_id',
        'payload',
        'user_id',
        'source_id',
        'status',
        'approved_at',
        'approved_by',
    ];

    public function singleton(): BelongsTo
    {
        return $this->belongsTo(Singleton::class, 'singleton_id');
    }

    public function getTable(): string
    {
        return Modularous::config('tables.singleton_revisions', 'modularous_singleton_revisions');
    }
}
