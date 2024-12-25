<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\IsAuthorizedable;

class ChatMessage extends Model
{
    use IsAuthorizedable, HasFileponds;

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
        'content'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['user_profile', 'attachments'];

    /**
     * Perform any actions when booting the trait
     *
     * @return void
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
            get: fn ($value) => get_user_profile($this->user),
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
        return unusualConfig('tables.chat_messages', parent::getTable());
    }

}
