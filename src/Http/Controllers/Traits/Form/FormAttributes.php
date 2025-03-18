<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Form;

use Illuminate\Support\Collection;

trait FormAttributes
{
    /**
     * @var array
     */
    protected $defaultFormAttributes = [];

    /**
     * Get the form attributes
     * @return array
     */
    public function getFormAttributes(): array
    {
        if ((bool) $this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve($this->defaultFormAttributes, object_to_array($this->getConfigFieldsByRoute('form_options', [])))
                )->toArray();
            } catch (\Throwable $th) {
                return [];
            }
        }

        return [];
    }

}
