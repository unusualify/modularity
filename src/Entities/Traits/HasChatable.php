<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Chat;
use Unusualify\Modularity\Entities\ChatMessage;

trait HasChatable
{
    /**
     * Perform any actions when booting the trait
     */
    public static function bootHasChatable(): void
    {
        static::retrieved(function (Model $model) {
            if ($model->chat) {
                $model->setAttribute('_chat_id', $model->chat->id);
            } elseif ($model->{$model->getKeyName()}) {
                $chat = $model->chat()->create();
                $model->setAttribute('_chat_id', $chat->id);
            }
        });

        static::creating(function (Model $model) {
            // dd('creating', $model);
        });

        static::created(function (Model $model) {
            $model->chat()->create();
        });

        static::updating(function (Model $model) {});

        static::updated(function (Model $model) {});

        static::saving(function (Model $model) {
            $model->offsetUnset('_chat_id');
        });

        static::saved(function (Model $model) {
            // dd('saved', $model);
        });

        static::restoring(function (Model $model) {});

        static::restored(function (Model $model) {});

        static::replicating(function (Model $model) {});

        static::deleting(function (Model $model) {});

        static::deleted(function (Model $model) {});

        static::forceDeleting(function (Model $model) {});

        static::forceDeleted(function (Model $model) {});
    }

    /**
     * Laravel hook to initialize the trait
     */
    public function initializeHasChatable(): void {}

    public function chat(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Chat::class, 'chatable');
    }

    public function chatMessages(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ChatMessage::class, Chat::class, 'chatable_id', 'chat_id', 'id', 'id');
    }
}
