<?php

namespace Modules\SystemNotification\Blueprint\MyNotification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyNotificationIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Subject',
                'key' => 'subject',
                'formatter' => [
                    'shorten',
                    30,
                ],
                'searchKey' => 'data->subject',
                'searchable' => true,
            ],
            // [
            //     'title' => 'Message',
            //     'key' => 'message',
            //     'formatter' => [
            //         'shorten',
            //         30,
            //     ],
            //     'searchKey' => 'data->message',
            //     'searchable' => true,
            // ],
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
                    'long',
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
