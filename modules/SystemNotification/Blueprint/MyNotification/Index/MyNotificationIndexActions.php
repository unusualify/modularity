<?php

namespace Modules\SystemNotification\Blueprint\MyNotification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyNotificationIndexActions implements ModuleRouteBlueprintProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'bulk-mark-read' => [
                'label' => __('Mark All as Read'),
                'forceLabel' => true,
                'icon' => 'mdi-check',
                'color' => 'success',
                'variant' => 'outlined',
                'density' => 'comfortable',
                'href' => 'admin.system.system_notification.my_notification.bulkMarkRead',
                'target' => '_self',
            ],
        ];
    }
}
