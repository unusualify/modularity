<?php

namespace Modules\SystemNotification\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Unusualify\Modularity\Entities\Model;

class Notification extends Model
{
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'is_read',
        'is_mine',
        'message',
        'html_message',
        'redirector',
        'has_redirector',
        'redirector_text',
    ];

    /**
     * Get the notifiable entity that the notification belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read()
    {
        return $this->read_at !== null;
    }

    protected function isRead() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->read_at !== null,
        );
    }

    protected function isMine() : Attribute
    {
        $user = auth()->user() ?? null;

        return new Attribute(
            get: fn ($value) => $user ? ($this->notifiable_type === $user->getMorphClass() && $this->notifiable_id == $user->id) : false,
        );
    }

    protected function message() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['message'] ?? '',
        );
    }

    protected function htmlMessage() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['htmlMessage'] ?? '',
        );
    }

    protected function redirector() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['redirector'] ?? '',
        );
    }

    protected function hasRedirector() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['hasRedirector'] ?? false,
        );
    }

    protected function redirectorText() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['redirectorText'] ?? '',
        );
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread()
    {
        return $this->read_at === null;
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead(Builder $query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread(Builder $query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeMyNotification(Builder $query)
    {
        return $query->where('notifiable_type', auth()->user()->getMorphClass())
            ->where('notifiable_id', auth()->user()->id);
    }

    public function scopeCompanyNotification(Builder $query)
    {
        $company = auth()->user()->company;

        if($company){
            return $query->where('notifiable_type', auth()->user()->getMorphClass())
                ->whereIn('notifiable_id', $company->users->pluck('id'));
        }

        return $query->myNotification();
    }

    /**
     * Create a new database notification collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Notifications\DatabaseNotificationCollection
     */
    public function newCollection(array $models = [])
    {
        return new DatabaseNotificationCollection($models);
    }
}
