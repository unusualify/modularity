<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SystemNotification\Events\AuthorizableCreated;
use Modules\SystemNotification\Events\AuthorizableUpdated;

class Authorization extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'authorized_id',
        'authorized_type',
        'authorizable_id',
        'authorizable_type',
    ];

    /**
     * Perform any actions when booting the trait
     */
    public static function booted(): void
    {

        static::created(function (Model $model) {
            AuthorizableCreated::dispatch($model);
        });

        static::updated(function (Model $model) {
            if ($model->isDirty('authorized_id') || $model->isDirty('authorized_type')) {
                AuthorizableUpdated::dispatch($model);
            }
        });
    }

    public function authorized()
    {
        return $this->morphTo();
    }

    public function authorizable()
    {
        return $this->morphTo();
    }

    public function getTable()
    {
        return modularityConfig('tables.authorizations', 'modularity_authorizations');
    }
}
