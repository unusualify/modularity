<?php

namespace Modules\SystemNotification\Blueprint\MyNotification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyNotificationIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'subtitle' => __('You can easily monitor the entire process by following the notifications.'),

            'createOnModal' => false,
            'editOnModal' => true,
            'isRowEditing' => false,
            'rowActionsType' => 'inline',
        ];
    }
}
