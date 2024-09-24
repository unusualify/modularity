<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            $model->id = (string)Str::orderedUuid();
            // dd($model);
        });
    }

    public function getIncrementing(): bool {
        return false;
    }

    public function getKeyType(): string {
        return 'string';
    }

}
