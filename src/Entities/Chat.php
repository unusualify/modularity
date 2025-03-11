<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Chat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chatable_id',
        'chatable_type',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['attachments'];

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function fileponds(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Filepond::class, ChatMessage::class, 'chat_id', 'filepondable_id', 'id');
    }

    public function attachments(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds()->whereRole('attachments')->get()->map(function ($filepond) {
                return $filepond->mediableFormat();
            }),
        );
    }

    public function chatable()
    {
        return $this->morphTo();
    }

    public function pinnedMessage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->messages()->where('is_pinned', 1)->first(),
        );
    }

    public function getTable()
    {
        return modularityConfig('tables.chats', parent::getTable());
    }
}
