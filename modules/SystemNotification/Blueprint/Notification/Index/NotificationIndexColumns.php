<?php

namespace Modules\SystemNotification\Blueprint\Notification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class NotificationIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Message',
                'key' => 'data.message',
                'searchable' => true,
                'formatter' => [
                    'shorten',
                    10,
                ],
            ],
            [
                'title' => 'Read',
                'key' => 'is_read',
                'formatter' => [
                    'status',
                ],
            ],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => [
                    'date',
                    'medium',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }
}
