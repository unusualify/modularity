<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
    ];

    protected $casts = [
        'status' => ProcessStatus::class,
    ];

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

    public function getTable(): string
    {
        return modularityConfig('tables.processes', 'm_processes');
    }
}
