<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Unusualify\Modularity\Traits\Allowable;

trait TableColumns
{
    use Allowable;

    /**
     * @var array
     */
    protected $indexTableColumns;

    /**
     * Get the index table columns for the table
     *
     * @return array
     */
    public function getIndexTableColumns()
    {
        if ((bool) $this->indexTableColumns) {
            return $this->indexTableColumns;
        } elseif (! $this->config) {
            return [];
        } else {
            $headers = Collection::make($this->getConfigFieldsByRoute('headers'))
                ->map(fn ($item) => (object) [...(array) $item, 'visible' => true]);

            $visibleColumns = explode(',', $this->request->get('columns') ?? $headers->pluck('key')->implode(','));

            return $this->indexTableColumns = $headers->reduce(function ($carry, $item) {
                $header = $this->getHeader((array) $item);
                if (isset($item->key)) {
                    // if ($item->key !== 'actions' && ! in_array($item->key, $visibleColumns)) {
                    //     $header['visible'] = false;
                    // }
                    $carry[] = $header;
                }

                return $carry;
            }, []);
        }

    }

    /**
     * Get the header for the table
     *
     * @param array $header
     * @return array
     */
    protected function getHeader($header)
    {
        return array_merge_recursive_preserve(
            modularityConfig('default_header'),
            $this->hydrateHeader($header)
        );
    }

    /**
     * Hydrate the header
     *
     * @param array $header
     * @return array
     */
    protected function hydrateHeader($header)
    {
        $this->hydrateHeaderSuffix($header);
        // add edit functionality to table title cell
        if ($this->titleColumnKey == $header['key'] && ! isset($header['formatter'])) {
            $header['formatter'] = [
                'edit',
            ];
        }

        // switch column
        if (isset($header['formatter']) && count($header['formatter']) && $header['formatter'][0] == 'switch') {
            $header['width'] = '20px';
            // $header['align'] = 'center';
        }

        if (isset($header['sortable']) && $header['sortable']) {
            if (preg_match('/(.*)(_relation)/', $header['key'], $matches)) {
                $header['sortable'] = false;
            }

        }

        if ($header['key'] == 'actions') {
            $header['width'] ??= 100;
            $header['align'] ??= 'center';
            $header['sortable'] ??= false;
        }

        return $header;
    }

    /**
     * Hydrate the header suffix
     *
     * @param array $header
     * @return void
     */
    protected function hydrateHeaderSuffix(&$header)
    {
        if ($this->isRelationField($header['key'])) {
            $header['key'] .= '_relation';
        }

        if (method_exists($this->repository->getModel(), 'isTimestampColumn') && $this->repository->isTimestampColumn($header['key'])) {
            $header['key'] .= '_timestamp';
        }

        // add uuid suffix for formatting on view
        if ($header['key'] == 'id' && $this->repository->hasModelTrait('Unusualify\Modularity\Entities\Traits\HasUuid')) {
            $header['key'] .= '_uuid';
            $header['formatter'] ??= ['edit'];
        }

    }

    /**
     * Dehydrate the header suffix
     *
     * @param array $header
     * @return void
     */
    protected function dehydrateHeaderSuffix(&$header)
    {
        $header['key'] = preg_replace('/_relation|_timestamp|_uuid/', '', $header['key']);
    }

    /**
     * Filters the headers based on the user's roles.
     *
     * This method checks each header item to determine if the current user
     * has the necessary permissions to view it. If the user is a super admin
     * or if the header does not have any role restrictions, the header will
     * be included in the returned array. Otherwise, it will be excluded.
     *
     * @param array $headers The array of header items to filter.
     * @return array The filtered array of header items.
     */
    public function filterHeadersByRoles($headers)
    {
        return $this->getAllowableItems(
            items: $headers,
            searchKey: 'allowedRoles',
            orClosure: fn ($item) => $this->user->isSuperAdmin(),
        );
    }
}
