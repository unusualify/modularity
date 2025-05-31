<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SystemNotification\Events\ProcessHistoryCreated;
use Modules\SystemNotification\Events\ProcessHistoryUpdated;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;

class ProcessHistory extends Model
{
    protected $fillable = [
        'status',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'status' => ProcessStatus::class,
    ];

    public static function booted(): void
    {
        static::updated(function (ProcessHistory $processHistory) {
            ProcessHistoryUpdated::dispatch($processHistory);
        });

        static::created(function (ProcessHistory $processHistory) {
            ProcessHistoryCreated::dispatch($processHistory);
        });
    }
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
