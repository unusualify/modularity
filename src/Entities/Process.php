<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
// use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;
use Unusualify\Modularity\Entities\Scopes\ProcessScopes;

class Process extends Model
{
    use ProcessScopes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'processable_id',
        'processable_type',
        'status',
        'reason',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'status_icon',
        'status_card_variant',
        'status_card_color',
        'status_reason_label',
        'status_informational_message',
        'next_action_label',
        'next_action_color',
        'status_dialog_titles',
        'status_dialog_messages',
    ];

    protected $casts = [
        'status' => ProcessStatus::class,
    ];

    // public static function booted(): void
    // {
    //     static::creating(function (Process $process) {
    //         $table = $process->getTable();
    //         if (DB::table($table)->where('processable_id', $process->processable_id)->where('processable_type', $process->processable_type)->exists()) {
    //             throw new \Exception('Processable model already exists');
    //         }
    //     });
    // }

    /**
     * Get the parent processable model
     */
    public function processable(): MorphTo
    {
        return $this->morphTo();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProcessHistory::class);
    }

    public function lastHistory(): HasOne
    {
        // #TODO: This is a temporary fix to get the latest history
        // $processTable = $this->getTable();
        // $processHistoryTable = (new ProcessHistory)->getTable();

        return $this->hasOne(ProcessHistory::class)
            ->latest();
        // ->whereRaw("{$processHistoryTable}.created_at = (select max(created_at) from {$processHistoryTable} where {$processHistoryTable}.process_id = {$processTable}.id)");
    }

    protected function statusLabel(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->label(),
        );
    }

    protected function statusColor(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->color(),
        );
    }

    protected function statusIcon(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->icon(),
        );
    }

    protected function statusCardVariant(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->cardVariant(),
        );
    }

    protected function statusCardColor(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->cardColor(),
        );
    }

    protected function statusReasonLabel(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->statusReasonLabel(),
        );
    }

    protected function nextActionLabel(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->nextActionLabel(),
        );
    }

    protected function nextActionColor(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->nextActionColor(),
        );
    }

    protected function statusInformationalMessage(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status->informationalMessage(),
        );
    }

    protected function statusDialogTitles(): Attribute
    {
        return new Attribute(
            get: fn () => [
                ProcessStatus::PREPARING->value => 'Preparing',
                ProcessStatus::WAITING_FOR_CONFIRMATION->value => 'Waiting for Confirmation',
                ProcessStatus::WAITING_FOR_REACTION->value => 'Waiting for Reaction',
                ProcessStatus::REJECTED->value => 'Rejected',
                ProcessStatus::CONFIRMED->value => 'Confirmed',
            ],
        );
    }

    protected function statusDialogMessages(): Attribute
    {
        return new Attribute(
            get: fn () => [
                ProcessStatus::PREPARING->value => __('The contents are being prepared or updated. Please check back later.'),
                ProcessStatus::WAITING_FOR_CONFIRMATION->value => __('Are you sure you want to send the process for confirmation?'),
                ProcessStatus::WAITING_FOR_REACTION->value => __('Are you sure you want to send the process for reaction?'),
                ProcessStatus::REJECTED->value => __('Are you sure you want to reject the process?'),
                ProcessStatus::CONFIRMED->value => __('Are you sure you want to confirm the process?'),
            ],
        );
    }

    public function getTable(): string
    {
        return modularityConfig('tables.processes', 'm_processes');
    }
}
