<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Table;

use Unusualify\Modularous\Entities\Traits\HasPosition;

trait TableDraggable
{
    /**
     * Get the table draggable options for the index view.
     *
     * @return array{draggable: bool, orderKey?: string}
     */
    protected function getTableDraggableOptions(): array
    {
        if ($this->repository) {
            $hasPosition = classHasTrait($this->repository->getModel(), HasPosition::class);

            return [
                'draggable' => $hasPosition && $this->getIndexOption('reorder'),
                'orderKey' => 'position',
            ];
        }

        return [
            'draggable' => false,
        ];
    }

    /**
     * Whether this index table supports drag-reorder (HasPosition + reorder option).
     */
    protected function isDraggableTable(): bool
    {
        return (bool) ($this->getTableDraggableOptions()['draggable'] ?? false);
    }

    /**
     * Whether search, status/advanced filters, or grouping prevent drag-reorder mode.
     */
    protected function hasActiveIndexListConstraints(): bool
    {
        $search = $this->request->get('search');

        if (is_string($search) && trim($search) !== '') {
            return true;
        }

        $filters = method_exists($this, 'getRequestFilters') ? $this->getRequestFilters() : [];

        $status = $filters['status'] ?? null;

        if ($status !== null && $status !== '' && $status !== 'all') {
            return true;
        }

        $filter = $this->request->get('filter');

        if (is_string($filter)) {
            $filter = json_decode($filter, true) ?? [];
        }

        if (is_array($filter)) {
            foreach ($filter as $key => $value) {
                if ($key === 'status') {
                    continue;
                }

                if ($value !== null && $value !== '' && $value !== [] && $value !== false) {
                    return true;
                }
            }
        }

        if (! empty($this->request->get('groupBy'))) {
            return true;
        }

        if (array_key_exists('columns', $filters) && ! empty($filters['columns'])) {
            return true;
        }

        if (array_key_exists('relations', $filters) && ! empty($filters['relations'])) {
            return true;
        }

        return false;
    }

    /**
     * Drag-reorder requires the full ordered list (no pagination).
     */
    protected function shouldUseUnpaginatedDraggableIndex(): bool
    {
        return $this->isDraggableTable() && ! $this->hasActiveIndexListConstraints();
    }

    /**
     * Resolve itemsPerPage for index item queries (HTML bootstrap vs AJAX).
     */
    protected function resolveIndexQueryPerPage(): int
    {
        if (! $this->request->ajax()) {
            return 0;
        }

        if ($this->shouldUseUnpaginatedDraggableIndex()) {
            return -1;
        }

        return (int) ($this->request->get('itemsPerPage') ?? $this->resolveIndexItemsPerPage());
    }
}
