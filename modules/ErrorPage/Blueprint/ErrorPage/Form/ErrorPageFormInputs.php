<?php

namespace Modules\ErrorPage\Blueprint\ErrorPage\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class ErrorPageFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'rules' => 'required|max:255',
            ],
            [
                'name' => 'error_code',
                'label' => 'Error Code',
                'type' => 'select',
                'rules' => 'required|in:403,404,500',
                'itemValue' => 'value',
                'itemTitle' => 'title',
                'items' => [
                    ['value' => '404', 'title' => '404 — Not Found'],
                    ['value' => '403', 'title' => '403 — Forbidden'],
                    ['value' => '500', 'title' => '500 — Server Error'],
                ],
            ],
        ];
    }
}
