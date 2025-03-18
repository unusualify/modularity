<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Unusualify\Modularity\Entities\Enums\Permission;

trait TableBulkActions
{
    /**
     * Bulk actions for the table when selected items are present
     * @return array
     */
    protected function getTableBulkActions(): array
    {
        $actions = [];

        if ($this->getIndexOption('delete')) {
            $actions[] = [
                'name' => 'bulkDelete',
                'can' => $this->permissionPrefix(Permission::DELETE->value),
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

        return $actions;
    }
}
