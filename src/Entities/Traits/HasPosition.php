<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\DB;

trait HasPosition
{
    protected static function bootHasPosition()
    {
        static::creating(function ($model) {
            if (! $model->position) {
                $model->setToLastPosition();
            } else {
                $model->position = (int) $model->position;

                if ($model->position > $model->getCurrentLastPosition()) {
                    $model->setToLastPosition();
                } else {
                    static::where('position', '>=', $model->position)->update(['position' => DB::raw('position + 1')]);
                }
            }
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
            throw new \Exception('You must pass an array to setNewOrder');
        }

        foreach ($ids as $id) {
            $model = static::findOrFail($id);
            $model->position = $startOrder++;
            $model->save();
        }

        return 1;
    }
}
