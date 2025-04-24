<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Scopes\ChatMessageScopes;

class ChatMessage extends Model
{
    use HasCreator,
        HasFileponds,
        ChatMessageScopes;

    protected static $abortCreatorRoleExceptions = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'is_read',
        'is_starred',
        'is_pinned',
        'is_sent',
        'is_received',
        'edited_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['user_profile', 'attachments'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_pinned' => 'boolean',
        'is_sent' => 'boolean',
        'is_received' => 'boolean',
        'edited_at' => 'datetime',
    ];

    /**
     * Perform any actions when booting the trait
     */
    public static function booted(): void
    {

        static::updated(function (Model $model) {
            if ($model->isDirty('is_pinned') && $model->is_pinned) {
                $model->chat->messages()->where('id', '!=', $model->id)->update([
                    'is_pinned' => 0,
                ]);
            }
        });
    }

    public function chat(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    protected function userProfile(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->creator ? get_user_profile($this->creator) : null,
        );
    }

    protected function attachments(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds()->whereRole('attachments')->get()->map(function ($filepond) {
                return $filepond->mediableFormat();
            }),
        );
    }

    public function getTable()
    {
        return modularityConfig('tables.chat_messages', parent::getTable());
    }
}
