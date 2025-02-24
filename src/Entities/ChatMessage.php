<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Entities\Traits\HasFileponds;

class ChatMessage extends Model
{
    use HasCreator, HasFileponds;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['user_profile', 'attachments'];

    /**
     * Perform any actions when booting the trait
     */
    public static function booted(): void
    {
        // static::retrieved(function (Model $model) {
        //     // dd('retrieved', $model->chatMessages);
        //     dd(
        //         $model->user,
        //         get_user_profile($model->user)
        //     );
        //     $model->setAttribute('_chat_id', $model->chat->id);
        //     $model->setAttribute('_chat_id', $model->chat->id);
        // });

        // static::creating(function (Model $model) {
        //     // dd('creating', $model);
        // });

        // static::created(function (Model $model) {
        //     $model->chat()->create();
        // });
    }

    public function chat(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    protected function userProfile(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => get_user_profile($this->creator),
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
