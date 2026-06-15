<?php

namespace Modules\SystemUser\Http\Requests;

use Closure;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Http\Requests\Request;

class CapabilityRouteRequest extends Request
{
    public function rulesForAll()
    {
        return [];
    }

    public function rulesForCreate()
    {
        $tableName = $this->model->getTable();

        return [
            'route_name' => [
                'required',
                'string',
                'min:2',
                "unique:{$tableName},route_name",
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_string($value) || ! Route::has($value)) {
                        $fail(__('The selected route name must be a named Laravel route.'));
                    }
                },
            ],
        ];
    }

    public function rulesForUpdate()
    {
        $tableName = $this->model->getTable();

        return [
            'route_name' => [
                'required',
                'string',
                'min:2',
                "unique:{$tableName},route_name,{$this->id}",
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_string($value) || ! Route::has($value)) {
                        $fail(__('The selected route name must be a named Laravel route.'));
                    }
                },
            ],
        ];
    }
}
