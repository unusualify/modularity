<?php

namespace Unusualify\Modularity\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Entities\Enums\AssignmentStatus;

class Assignment extends Model
{

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

    protected $appends = [
        'assignee_name',
        'assignee_avatar',
        'assigner_name',

        'status_label',
        'status_color',
        'status_icon',
        'status_interval_description',
        'status_vuetify_icon',
    ];

    protected $casts = [
        'status' => AssignmentStatus::class,

        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public static function booted()
    {
        static::creating(function ($assignment) {
            $guard = Auth::guard();
            $assignment->assigner_id = $guard->id();
            $assignment->assigner_type = get_class(auth()->user());
        });
    }

    public function assignable()
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
            get: fn ($value) => $this->status->label(),
        );
    }

    protected function statusColor(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status->color(),
        );
    }

    protected function statusIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status->icon(),
        );
    }

    protected function statusIntervalDescription(): Attribute
    {
        $timeKey = 'due_at';

        switch($this->status) {
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
            get: fn ($value) => $this->status->timeIntervalDescription() . ': '
                . "<span class='{$this->status->timeClasses()}'>{$formattedTime}</span>",
        );
    }

    protected function statusVuetifyIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => "<v-icon icon='{$this->status->icon()}' color='{$this->status->iconColor()}'/>",
        );
    }

    public function assignee()
    {
        return $this->morphTo();
    }

    protected function assigneeName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->assignee->name ?? $this->assignee->email,
        );
    }

    protected function assigneeAvatar(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->assignee->avatar,
        );
    }

    protected function assignerName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->assigner->name ?? $this->assigner->email,
        );
    }

    public function assigner()
    {
        return $this->morphTo();
    }

    public function scopeIsAssigneeType($query, $type)
    {
        return $query->where('assignee_type', $type);
    }

    public function scopeIsAssignee($query, $user)
    {
        return $query->where('assignee_id', $user->id)
            ->where('assignee_type', get_class($user));
    }

    public function scopeIsAssigneeRole($query, $roles)
    {
        return $query->whereHas('assignee', function ($query) use ($roles) {
            $query->role($roles);
        });
    }

    public function scopeIsCompleted($query)
    {
        return $query->where('status', AssignmentStatus::COMPLETED);
    }

    public function scopeIsPending($query)
    {
        return $query->where('status', AssignmentStatus::PENDING);
    }

    public function scopeIsCancelled($query)
    {
        return $query->where('status', AssignmentStatus::REJECTED);
    }

    public function getTable()
    {
        return modularityConfig('tables.assignments', 'm_assignments');
    }
}
