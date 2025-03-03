<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;
use Unusualify\Modularity\Entities\Process;
use Unusualify\Modularity\Entities\ProcessHistory;

trait Processable
{
    use HasFileponds;

    /**
     * Perform any actions when booting the trait
     */
    public static function bootProcessable(): void
    {
        static::retrieved(function (Model $model) {});

        static::saving(function (Model $model) {});

        static::created(function (Model $model) {
            if (! $model->process()->exists()) {
                $model->process()->create([
                    'status' => ProcessStatus::PREPARING,
                ]);
            }
        });

        static::saved(function (Model $model) {
            if ($model->processable_status) {
                dd($model->processable_status, $model->processable_reason);
                $model->setProcessStatus($model->processable_status, $model->processable_reason);
            }
        });

        // static::saved(function (Model $model) {
        //     $model->process()->createOrUpdate ([
        //         'status' => ProcessStatus::PREPARING,
        //     ]);
        // });
    }

    /**
     * Laravel hook to initialize the trait
     */
    public function initializeProcessable(): void {}

    public function process(): MorphOne
    {
        return $this->morphOne(Process::class, 'processable');
    }

    /**
     * Get all process histories for this model through the Process model
     */
    public function processHistories(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProcessHistory::class,
            Process::class,
            'processable_id', // Foreign key on processes table
            'process_id',     // Foreign key on process_histories table
            'id',             // Local key on the model using this trait
            'id'              // Local key on processes table
        )->where(modularityConfig('tables.processes', 'm_processes') . '.processable_type', static::class);
    }

    /**
     * Set the process status
     */
    public function setProcessStatus(string $status, ?string $reason = null): void
    {
        // if( !$this->process()->exists()) {
        //     $this->process()->create([
        //         'status' => ProcessStatus::PREPARING,
        //     ]);
        // } else {

        //     // Update the current status
        // }

        $this->process()->updateOrCreate(
            ['processable_id' => $this->id, 'processable_type' => static::class],
            ['status' => $status]
        );

        $this->process->histories()->create([
            'status' => $status,
            'reason' => $reason,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Send for confirmation
     */
    public function sendForConfirmation(): void
    {
        $this->setProcessStatus(ProcessStatus::get('WAITING_FOR_CONFIRMATION'), null);
    }

    /**
     * Confirm the process
     */
    public function confirm(): void
    {
        $this->setProcessStatus(ProcessStatus::get('CONFIRMED'));
    }

    /**
     * Reject the process
     */
    public function reject(string $reason): void
    {
        $this->setProcessStatus(ProcessStatus::get('REJECTED'), $reason);
    }

    /**
     * Check if the process is in a specific status for a country
     */
    public function hasProcessStatus(string $status): bool
    {
        return $this->process()
            ->where('status', $status)
            ->exists();
    }
}
