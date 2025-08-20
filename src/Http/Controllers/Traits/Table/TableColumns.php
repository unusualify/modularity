<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Unusualify\Modularity\Hydrates\HeaderHydrator;
use Unusualify\Modularity\Traits\Allowable;

trait TableColumns
{
    use Allowable;

    /**
     * @var array
     */
    protected static $tableHeadersCallbacks = [];

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

            $headers = method_exists($this, 'tableHeaders')
                ? $this->tableHeaders($headers)
                : $headers;

            if (isset(static::$tableHeadersCallbacks[static::class]) && is_callable(static::$tableHeadersCallbacks[static::class])) {
                $headers = call_user_func(static::$tableHeadersCallbacks[static::class], $headers->toArray());
            }

            if (is_array($headers)) {
                $headers = Collection::make($headers);
            }

            $headers = $headers->reduce(function ($carry, $item) {
                $header = $this->getHeader((array) $item);

                if (isset($item->key)) {
                    $carry[] = $header;
                }

                return $carry;
            }, []);

            return $this->indexTableColumns = $headers;
        }

    }

    /**
     * Update the table headers
     *
     * @return void
     */
    public static function updateTableHeaders(callable $callback)
    {
        static::$tableHeadersCallbacks[static::class] = $callback;
    }

    /**
     * Get the header for the table
     *
     * @param array $header
     * @return array
     */
    protected function getHeader($header)
    {
        $this->hydrateHeaderSuffix($header);

        // add edit functionality to table title cell
        if ($this->titleColumnKey == $header['key'] && ! isset($header['formatter'])) {
            $header['formatter'] = [
                'edit',
            ];
        }

        $header = (new HeaderHydrator($header, $this->module, $this->routeName))->hydrate();

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
            $itemTitle = $header['itemTitle'] ?? 'name';
            $header['key'] .= '_relation_' . $itemTitle;
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
