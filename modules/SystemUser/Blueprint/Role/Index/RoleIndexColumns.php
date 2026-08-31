<?php

namespace Modules\SystemUser\Blueprint\Role\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class RoleIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Title',
                'key' => 'title',
                'align' => 'start',
                'sortable' => false,
                'filterable' => false,
                'groupable' => false,
                'divider' => false,
                'class' => '', // || []
                'cellClass' => '', // || []
                // 'width' => '', // || int
                // vuetify datatable header fields end

                // custom fields for ue-datatable start
                'searchable' => true,
                'isRowEditable' => false,
                'isColumnEditable' => false,
                'formatter' => [
                    0 => 'edit',
                ],
                // custom fields for ue-datatable end
            ],
            [
                'title' => 'Name',
                'key' => 'name',
                'align' => 'start',
                'sortable' => false,
                'filterable' => false,
                'groupable' => false,
                'divider' => false,
                'class' => '', // || []
                'cellClass' => '', // || []
                // 'width' => '', // || int
                // vuetify datatable header fields end

                // custom fields for ue-datatable start
                'searchable' => true,
                'isRowEditable' => false,
                'isColumnEditable' => false,
                'formatter' => [
                    0 => 'edit',
                ],
                // custom fields for ue-datatable end
            ],
            [
                // vuetify datatable header fields start
                'title' => 'Guard Name',
                'key' => 'guard_name',
                'align' => 'start',
                'sortable' => false,
                'filterable' => false,
                'groupable' => false,
                'divider' => false,
                'class' => '', // || []
                'cellClass' => '', // || []
                // 'width' => '', // || int
                // vuetify datatable header fields end

                // custom fields for ue-datatable start
                'searchable' => true,
                'isRowEditable' => false,
                'isColumnEditable' => false,
                'removable' => true,
                'formatter' => [],
                // custom fields for ue-datatable end
            ],
            // [
            //     'title' => 'Created Time',
            //     'key' => 'created_at',
            //     'sortable' => true,
            //     'filterable' => true,
            //     'groupable' => false,
            //     'divider' => false,
            //     'class' => '', // || []
            //     'cellClass' => '', // || []
            //     'width' => '', // || int
            //     // vuetify datatable header fields end

            //     // custom fields for ue-datatable start
            //     'searchable' => true,
            //     'isRowEditable' => false,
            //     'isColumnEditable' => false,
            //     'formatter' => ['date', 'long'],
            //     // custom fields for ue-datatable end
            // ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'width' => 50,
                'sortable' => false,
            ],
        ];
    }
}
