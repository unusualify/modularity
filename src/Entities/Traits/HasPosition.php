<?php

namespace Unusualify\Modularity\Entities\Traits;

trait HasPosition
{
    protected static function bootHasPosition()
    {
        static::creating(function ($model) {
            // dd($model);
            $model->setToLastPosition();
        });
    }

    protected function setToLastPosition()
    {
        $this->position = $this->getCurrentLastPosition() + 1;
    }

    protected function getCurrentLastPosition()
    {
        return (int) static::max("{$this->getTable()}.position");
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy("{$this->getTable()}.position");
    }

    /**
     * @param array $ids
     * @param int $startOrder
     * @return void
     */
    public static function setNewOrder($ids, $startOrder = 1)
    {
        if (! is_array($ids)) {
            throw new \Exception('You must pass an array to setNewOrder()');
        }

        if ($startOrder < 1) {
            throw new \Exception('$startOrder must be bigger or equal to 1');
        }

        foreach ($ids as $id) {
            $model = static::find($id);
            $model->position = $startOrder++;
            $model->save();
        }

        return 1;
    }
}
