<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Table;

use Unusualify\Modularous\Entities\Enums\Permission;

trait TableBulkActions
{
    /**
     * Bulk actions for the table when selected items are present
     */
    protected function getTableBulkActions(): array
    {
        $actions = [];

        if ($this->module) {
            if ($this->getIndexOption('delete')) {
                $actions[] = [
                    'name' => 'bulkDelete',
                    'can' => $this->module->generatePermissionMiddlewareDefinition(Permission::DELETE->value, $this->routeName),
                    'icon' => '$delete',
                    // 'color' => 'red darken-2',
                    'color' => 'primary',
                ];
            }

            if ($this->getIndexOption('forceDelete')) {
                $actions[] = [
                    'name' => 'bulkForceDelete',
                    'icon' => '$delete',
                    'can' => 'forceDelete',
                    // 'color' => 'red darken-2',
                    'color' => 'red',
                ];
            }

            if ($this->getIndexOption('restore')) {
                $actions[] = [
                    'name' => 'bulkRestore',
                    'icon' => '$restore',
                    'can' => 'restore',
                    // 'color' => 'red darken-2',
                    'color' => 'green',
                ];
            }
        }

        return $actions;
    }
}
