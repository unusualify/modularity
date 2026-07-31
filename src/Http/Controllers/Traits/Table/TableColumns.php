<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\Traits\HasPayment;
use Unusualify\Modularous\Hydrates\HeaderHydrator;
use Unusualify\Modularous\Traits\Allowable;

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

            if (is_array($headers)) {
                $headers = Collection::make($headers);
            }

            $headerItems = $headers->values()->all();

            if ($this->module && $this->repository && !is_null($headerItems)) {
                if (method_exists($this->repository, 'appendTableHeader')) {
                    $appended = $this->normalizeTableHeaderItems(
                        $this->repository->appendTableHeader($headerItems)
                    );
                    $headerItems = $this->mergeTableHeadersBeforeActions($headerItems, $appended);
                }
                if (method_exists($this->repository, 'prependTableHeader')) {
                    $prepended = $this->normalizeTableHeaderItems(
                        $this->repository->prependTableHeader($headerItems)
                    );
                    $headerItems = array_merge($prepended, $headerItems);
                }
            }

            $headers = Collection::make($headerItems);

            if (isset(static::$tableHeadersCallbacks[static::class]) && is_callable(static::$tableHeadersCallbacks[static::class])) {
                $headers = call_user_func(static::$tableHeadersCallbacks[static::class], $headers->toArray());
            }

            if (is_array($headers)) {
                $headers = Collection::make($headers);
            }

            $headers = $headers->reduce(function ($carry, $item) {
                $item = (object) [...(array) $item, 'visible' => ((array) $item)['visible'] ?? true];
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
     * @param  array<int, mixed>  $headers
     * @return array<int, object>
     */
    protected function normalizeTableHeaderItems(array $headers): array
    {
        return array_map(
            fn ($item) => (object) [...(array) $item, 'visible' => ((array) $item)['visible'] ?? true],
            $headers,
        );
    }

    /**
     * Insert appended headers before the actions column when present; otherwise append.
     *
     * @param  array<int, mixed>  $headers
     * @param  array<int, mixed>  $appended
     * @return array<int, mixed>
     */
    protected function mergeTableHeadersBeforeActions(array $headers, array $appended): array
    {
        if ($appended === []) {
            return $headers;
        }

        $actionsIndex = null;
        foreach ($headers as $index => $header) {
            $key = is_array($header)
                ? ($header['key'] ?? null)
                : (is_object($header) ? ($header->key ?? null) : null);

            if ($key === 'actions') {
                $actionsIndex = $index;
                break;
            }
        }

        if ($actionsIndex === null) {
            return array_merge($headers, $appended);
        }

        return array_merge(
            array_slice($headers, 0, $actionsIndex),
            $appended,
            array_slice($headers, $actionsIndex),
        );
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
     * Add relations on index page
     */
    protected function addIndexWithsTableColumns(): array
    {
        $withs = [];

        $rawHeaders = $this->getConfigFieldsByRoute('headers', []);

        if (count($rawHeaders) > 0) {
            $model = $this->repository->getModel();
            if (method_exists($model, 'hasRelation') || method_exists($model, 'definedRelations')) {
                foreach ($rawHeaders as $header) {
                    $header = (array) $header;

                    if (isset($header['with'])) {
                        $withs = $this->mergeIndexWiths(
                            $withs,
                            $this->resolveHeaderWiths($header['with'], $model)
                        );
                    }

                    $withs = $this->mergeIndexWiths(
                        $withs,
                        $this->deriveHeaderWithsFromDotNotation($header, $model)
                    );
                }
            }

            if (classHasTrait($model, HasPayment::class)
                && method_exists($model, 'getPaymentEagerLoads')) {
                $withs = $this->mergeIndexWiths($withs, $model->getPaymentEagerLoads());
            }
        }

        return $withs;
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
        $header['sourceKey'] = $header['sourceKey'] ?? $header['key'];

        if ($this->isRelationField($header['sourceKey'])) {
            $itemTitle = $header['itemTitle'] ?? 'name';
            $header['key'] = $header['key'] . '_' . $itemTitle;
            $header['sourceKey'] .= '_relation_' . $itemTitle;
        }

        if (method_exists($this->repository->getModel(), 'isTimestampColumn') && $this->repository->isTimestampColumn($header['sourceKey'])) {
            $header['sourceKey'] .= '_timestamp';
        }

        // add uuid suffix for formatting on view
        if ($header['sourceKey'] == 'id' && $this->repository->hasModelTrait('Unusualify\Modularous\Entities\Traits\HasUuid')) {
            $header['sourceKey'] .= '_uuid';
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
            orClosure: fn ($item) => $this->user->is_superadmin,
        );
    }
}
