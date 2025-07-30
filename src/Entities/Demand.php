<?php

namespace Unusualify\Modularity\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\SystemNotification\Events\DemandCreated;
use Modules\SystemNotification\Events\DemandUpdated;
use Unusualify\Modularity\Entities\Enums\DemandStatus;
use Unusualify\Modularity\Entities\Enums\DemandPriority;
use Unusualify\Modularity\Entities\Scopes\DemandScopes;
use Unusualify\Modularity\Entities\Traits\HasFileponds;

class Demand extends Model
{
    use DemandScopes, HasFileponds;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'demandable_id',
        'demandable_type',
        'demander_id',
        'demander_type',
        'responder_id',
        'responder_type',
        'status',
        'priority',
        'title',
        'description',
        'response',
        'response_at',
        'due_at',
        'resolved_at',
        'parent_id', // for Q&A thread functionality
    ];

    protected $appends = [
        'demander_name',
        'demander_avatar',
        'responder_name',
        'responder_avatar',

        'status_label',
        'status_color',
        'status_icon',
        'status_interval_description',
        'status_vuetify_icon',

        'priority_label',
        'priority_color',
        'priority_icon',
        'priority_vuetify_icon',

        'attachments',
        'has_response',
        'is_overdue',
        'days_until_due',
    ];

    protected $casts = [
        'status' => DemandStatus::class,
        'priority' => DemandPriority::class,
        'due_at' => 'datetime',
        'response_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public static function booted()
    {
        static::creating(function ($demand) {
            $guard = Auth::guard();
            $demand->demander_id = $guard->id();
            $demand->demander_type = get_class(auth()->user());

            $demand->status = $demand->status ?? DemandStatus::PENDING;
        });

        static::created(function ($demand) {
            DemandCreated::dispatch($demand);
        });

        static::updated(function ($demand) {
            DemandUpdated::dispatch($demand);
        });
    }

    // Relationships
    public function demandable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function demander(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function responder(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function thread(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->with('children');
    }

    // Accessors
    protected function demanderName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->demander->name ?? $this->demander->email,
        );
    }

    protected function demanderAvatar(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->demander->avatar ?? null,
        );
    }

    protected function responderName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->responder ? ($this->responder->name ?? $this->responder->email) : null,
        );
    }

    protected function responderAvatar(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->responder ? $this->responder->avatar : null,
        );
    }

    // Status Accessors
    protected function statusLabel(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status ? $this->status->label() : null,
        );
    }

    protected function statusColor(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status ? $this->status->color() : null,
        );
    }

    protected function statusIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status ? $this->status->icon() : null,
        );
    }

    protected function statusIntervalDescription(): Attribute
    {
        $timeKey = 'created_at';

        switch ($this->status) {
            case DemandStatus::ANSWERED:
            case DemandStatus::REJECTED:
                $timeKey = 'resolved_at';
                break;
            case DemandStatus::EVALUATED:
            case DemandStatus::IN_REVIEW:
                $timeKey = 'response_at';
                break;
        }

        $time = $this->{$timeKey};
        $formattedTime = $time ? $time->format('F j, Y') : 'N/A';

        return new Attribute(
            get: fn ($value) => $this->status ? $this->status->timeIntervalDescription() . ': '
                . "<span class='{$this->status->timeClasses()}'>{$formattedTime}</span>" : null,
        );
    }

    protected function statusVuetifyIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status ? "<v-icon icon='{$this->status->icon()}' color='{$this->status->iconColor()}'/>" : null,
        );
    }

    // Priority Accessors
    protected function priorityLabel(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->priority ? $this->priority->label() : null,
        );
    }

    protected function priorityColor(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->priority ? $this->priority->color() : null,
        );
    }

    protected function priorityIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->priority ? $this->priority->icon() : null,
        );
    }

    protected function priorityVuetifyIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->priority ? "<v-icon icon='{$this->priority->icon()}' color='{$this->priority->iconColor()}'/>" : null,
        );
    }

    // Other Accessors
    protected function attachments(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds()->whereRole('attachments')->get()->map(function ($filepond) {
                return $filepond->mediableFormat();
            }),
        );
    }

    protected function hasResponse(): Attribute
    {
        return new Attribute(
            get: fn ($value) => !empty($this->response),
        );
    }

    protected function isOverdue(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->due_at && $this->due_at->isPast() && $this->status->isActive(),
        );
    }

    protected function daysUntilDue(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->due_at ? now()->diffInDays($this->due_at, false) : null,
        );
    }

    // actions
    public function reject()
    {
        $this->update([
            'status' => DemandStatus::REJECTED,
            'resolved_at' => now(),
        ]);
    }

    public function answer($response)
    {
        $this->update([
            'status' => DemandStatus::ANSWERED,
            'response' => $response,
            'response_at' => now(),
        ]);
    }

    public function getTable()
    {
        return modularityConfig('tables.demands', 'um_demands');
    }
}
