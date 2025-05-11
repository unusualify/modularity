<?php

namespace Unusualify\Modularity\Hydrates;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class InputHydrator
{
    public function __construct(
        protected $input,
        protected $module,
        protected $routeName,
        protected $skipQueries = null,
    ) {}

    public function hydrate(): array
    {
        $input = $this->input;
        if (isset($input['type'])) {
            $hydrateClass = "Unusualify\Modularity\Hydrates\Inputs\\" . studlyName($input['type']) . 'Hydrate';
            $skipQueries = $this->skipQueries ?? Request::ajax();

            if (@class_exists($hydrateClass)) {
                $input = App::make($hydrateClass, [
                    'input' => $input,
                    'module' => $this->module ?? null,
                    'routeName' => $this->routeName ?? null,
                    'skipQueries' => $skipQueries,
                ])->render();
            }
        }

        return $input;
    }
}
