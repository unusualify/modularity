<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Illuminate\Support\Str;

trait InspectTraits
{
    /**
     * @param string $behavior
     * @return bool
     */
    public function hasBehavior($behavior)
    {
        $hasBehavior = classHasTrait($this, 'Unusualify\Modularity\Repositories\Traits\\' . ucfirst($behavior) . 'Trait');
        // dd($behavior, $hasBehavior, Str::startsWith($behavior, 'translation'));
        if (Str::startsWith($behavior, 'translation')) {
            $hasBehavior = $hasBehavior && $this->model->isTranslatable();
        }

        return $hasBehavior;
    }

    /**
     * @return bool
     */
    public function isTranslatable($column)
    {
        return method_exists($this->model, 'isTranslatable') && $this->model->isTranslatable($column);
    }

    /**
     * @return bool
     */
    public function isSoftDeletable()
    {
        return method_exists($this->model, 'isSoftDeletable') && $this->model->isSoftDeletable();
    }

    /**
     * @param string $class name resolution
     * @return bool
     */
    public function hasModelTrait($trait)
    {
        $hasTrait = classHasTrait($this->getModel(), $trait);

        return $hasTrait;
    }
}
