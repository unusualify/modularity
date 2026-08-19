<?php

namespace Modules\Cms\Blueprint\Redirect\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class RedirectFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            ['name' => 'from_path', 'label' => 'From path', 'type' => 'text', 'rules' => 'required'],
            ['name' => 'to_path', 'label' => 'To path', 'type' => 'text', 'rules' => 'required'],
            ['name' => 'locale', 'label' => 'Locale', 'type' => 'select', 'items' => getLocales(), 'rules' => 'required'],
            ['name' => 'status_code', 'label' => 'Status Code', 'type' => 'number', 'rules' => 'required|integer|in:301,302,307,308'],
            ['name' => 'is_active', 'label' => 'Active', 'type' => 'switch'],
        ];
    }
}
