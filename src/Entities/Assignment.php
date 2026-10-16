<?php

namespace Unusualify\Modularous\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Modules\SystemNotification\Events\AssignmentCreated;
use Modules\SystemNotification\Events\AssignmentUpdated;
use Unusualify\Modularous\Entities\Enums\AssignmentStatus;
use Unusualify\Modularous\Entities\Scopes\AssignmentScopes;
use Unusualify\Modularous\Entities\Traits\HasFileponds;

class Assignment extends Model
{
    use SoftDeletes, AssignmentScopes, HasFileponds;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assignable_id',
        'assignable_type',
        'assignee_id',
        'assignee_type',
        'assigner_id',
        'assigner_type',
        'status',
        'title',
        'description',
        'due_at',
        'accepted_at',
        'completed_at',
    ];

    protected $with = [
        'assignee',
        'assigner',
    ];

    protected $appends = [
        'assignee_name',
        'assignee_avatar',
        'assigner_name',

        'status_label',
        'status_color',
        'status_icon',
        'status_interval_description',
        'status_vuetify_icon',

        'attachments',
        'preliminaries',
    ];

    protected $casts = [
        'status' => AssignmentStatus::class,

        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public static function booted()
    {
        static::creating(function ($assignment) {
            if (Auth::check()) {
                $guard = Auth::guard();
                $assignment->assigner_id = $assignment->assigner_id ?? $guard->id();
                $assignment->assigner_type = $assignment->assigner_type ?? get_class(Auth::user());
            }
        });

        static::created(function ($assignment) {
            AssignmentCreated::dispatch($assignment);
        });

        static::updated(function ($assignment) {
            AssignmentUpdated::dispatch($assignment);
        });
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assigner(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): MorphTo
    {
        return $this->morphTo();
    }

    protected function dueAt(): Attribute
    {
        return new Attribute(
            set: fn ($value) => Carbon::parse($value)->format('Y-m-d H:i:s'),
        );
    }

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

    protected function statusIconColor(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status ? $this->status->iconColor() : null,
        );
    }

    protected function statusIntervalDescription(): Attribute
    {
        $timeKey = 'due_at';

        switch ($this->status) {
            case AssignmentStatus::COMPLETED:
                $timeKey = 'completed_at';

                break;
            case AssignmentStatus::CANCELLED:
            case AssignmentStatus::REJECTED:
                $timeKey = 'updated_at';

                break;
        }

        $time = $this->{$timeKey};

        $formattedTime = $time->format('F j, Y'); // This line is already correct and does not need to be updated
        // Alternatively, you can use the forHuman method of Carbon
        // return $time->forHuman();

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

    protected function assigneeName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->assignee?->name ?? $this->assignee?->email,
        );
    }

    protected function assigneeAvatar(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->assignee?->avatar,
        );
    }

    protected function assignerName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->assigner?->name ?? $this->assigner?->email,
        );
    }

    protected function attachments(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds->where('role', 'attachments')->map(function ($filepond) {
                return $filepond->mediableFormat();
            }),
        );
    }

    protected function preliminaries(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds->where('role', 'preliminaries')->map(function ($filepond) {
                return $filepond->mediableFormat();
            }),
        );
    }

    public function getTable()
    {
        return modularousConfig('tables.assignments', 'um_assignments');
    }
}
