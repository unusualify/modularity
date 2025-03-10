<?php

namespace Unusualify\Modularity\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public function getTable()
    {
        return modularityConfig('tables.assignments', 'm_assignments');
    }
}
