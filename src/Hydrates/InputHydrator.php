<?php

namespace Unusualify\Modularity\Hydrates;

use Illuminate\Support\Facades\App;

class InputHydrator
{
    public function __construct(
        protected $input,
        protected $module,
    ) {
    }

    public function hydrate(): array
    {
        $input = $this->input;
        if (isset($input['type'])) {
            $hydrateClass = "Unusualify\Modularity\Hydrates\Inputs\\" . studlyName($input['type']) . 'Hydrate';

            if (@class_exists($hydrateClass)) {
                $input = App::make($hydrateClass, [
                    'input' => $input,
                    'module' => $this->module ?? null,
                ])->render();
            }
        }

        return $input;
    }
}

