<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessHistory extends Model
{
    protected $fillable = [
        'status',
        'reason',
        'user_id',
    ];

    /**
     * Get the parent processable model
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * Get the user who made this change
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // /**
    //  * Get the processable model
    //  */
    // public function processable(): HasOneThrough
    // {
    //     return $this->hasOneThrough(Process::class, ProcessHistory::class);
    // }

    public function getTable(): string
    {
        return modularityConfig('tables.process_histories', 'm_process_histories');
    }
}
