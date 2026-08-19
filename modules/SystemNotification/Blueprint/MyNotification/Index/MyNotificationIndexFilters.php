<?php

namespace Modules\SystemNotification\Blueprint\MyNotification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyNotificationIndexFilters implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            // 'my-notification' => [
            //     'name' => 'Mine',
            //     'slug' => 'my-notification',
            //     'scope' => 'myNotification',
            // ],
            'read' => [
                'name' => __('Read'),
                'slug' => 'read',
                'scope' => 'read',
            ],
            'unread' => [
                'name' => __('Unread'),
                'slug' => 'unread',
                'scope' => 'unread',
            ],
        ];
    }
}
