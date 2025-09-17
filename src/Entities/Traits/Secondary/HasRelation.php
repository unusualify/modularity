<?php

namespace Unusualify\Modularity\Entities\Traits\Secondary;

trait HasRelation
{
    protected static function bootHasRelation()
    {
        static::forceDeleting(function ($model) {
            // dd($model);
            // dd($model);
            // $model->setToLastPosition();
            // $model->
        });
    }
}
