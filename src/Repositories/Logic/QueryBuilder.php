<?php

namespace Unusualify\Modularous\Repositories\Logic;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\Interfaces\Sortable;
use Unusualify\Modularous\Traits\SerializeModel;

trait QueryBuilder
{
    use MethodTransformers, SerializeModel;

    /**
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param int $perPage
     * @param bool $forcePagination
     * @param int|string|null $id
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function get($with = [], $scopes = [], $orders = [], $perPage = 20, $appends = [], $forcePagination = false, $id = null, $exceptIds = [])
    {
        $query = $this->model->query();

        $query = $this->model->with($this->formatWiths($query, $with));

        if (isset($scopes['searches']) && isset($scopes['search']) && is_array($scopes['searches'])) {
            $translatedAttributes = $this->model->translatedAttributes ?? [];

            // First, extract relationship fields (containing dots)
            $relationshipFields = [];
            $regularFields = [];

            foreach ($scopes['searches'] as $field) {
                if (str_contains($field, '.')) {
                    $relationshipFields[] = $field;
                } else {
                    $regularFields[] = $field;
                }
            }

            // Handle relationship field searching
            if (! empty($relationshipFields) && isset($scopes['search'])) {
                $this->searchInRelationships($query, $scopes, 'search', $relationshipFields);
            }

            // Remove translated attributes from regular fields for main table searching
            $searches = array_filter($regularFields, function ($field) use ($translatedAttributes) {
                return ! in_array($field, $translatedAttributes);
            });

            // Search in main table fields (non-translated, non-relationship)
            $this->searchIn($query, $scopes, 'search', $searches);

            // Handle translated fields (existing logic)
            $scope['searches'] = array_filter($regularFields, function ($field) use ($translatedAttributes) {
                return in_array($field, $translatedAttributes);
            });
        }

        $query = $this->filter($query, $scopes);

        if ($orders && count($orders) > 0) {
            $query = $this->order($query, $orders);
        } elseif (! $forcePagination && $this->model instanceof Sortable) {
            $query = $query->ordered();
        }

        $page = request()->get('page') ?? null;

        if ($id) {

            // Create a clone of the query to find the position of the record
            $cloneQuery = clone $query;

            // Get the position of the record
            if ($cloneQuery->where('id', $id)->exists()) {
                $cloneQuery = clone $query;

                // Get all IDs in the correct query order
                $orderedIds = $cloneQuery->pluck('id')->toArray();

                // Find the position of our target ID in the ordered results
                $position = array_search($id, $orderedIds);

                if ($position !== false) {
                    // Calculate which page the record is on (1-based pagination)
                    $page = (int) floor($position / $perPage) + 1;
                }
            }
        } elseif ($exceptIds) {
            $query = $query->whereNotIn('id', $exceptIds);
        }

        if ($perPage === 0) {
            return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
                'items' => collect(),
                'total' => $query->count(),
                'perPage' => -1,
                'currentPage' => 0,
                'options' => [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ],
            ]);
        }

        if ($perPage === -1) {
            $total = $query->count();

            return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
                'items' => $query->get(),
                'total' => $total,
                'perPage' => $total,
                'currentPage' => 1,
                'options' => [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ],
            ]);
        }

        $results = $query->paginate($perPage, page: $page);

        if (! empty($appends)) {
            // Apply appends/mutators
            $results->getCollection()->transform(function ($item) use ($appends) {
                // If $appends is a string, convert to array
                $appendArray = is_string($appends) ? explode(',', $appends) : $appends;

                foreach ($appendArray as $append) {
                    $item->{$append} = $item->{$append};
                }

                return $item;
            });
        }

        return $results;
    }

    /**
     * Get paginated results with optional caching (main entry point).
     * Automatically uses cached version if caching is enabled.
     *
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param int $perPage
     * @param array $appends
     * @param bool $forcePagination
     * @param int|string|null $id
     * @param array $exceptIds
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginator($with = [], $scopes = [], $orders = [], $perPage = 20, $appends = [], $forcePagination = false, $id = null, $exceptIds = [])
    {
        if (! $this->shouldUseCache('index')) {
            return $this->get($with, $scopes, $orders, $perPage, $appends, $forcePagination, $id, $exceptIds);
        }

        return $this->getCached($with, $scopes, $orders, $perPage, $appends, $forcePagination, $id, $exceptIds);
    }

    /**
     * Paginate using request parameters (itemsPerPage, page, scopes, orders, etc.).
     *
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(?Request $request = null)
    {
        $request = $request ?? request();

        $perPage = (int) ($request->get('itemsPerPage') ?? $request->get('per_page', 10));
        $scopes = $request->get('scopes', []);
        $orders = $request->get('orders', []);
        $with = $request->get('eager', $request->get('with', []));
        $appends = $request->get('appends', []);
        $exceptIds = $request->get('exceptIds', []);

        if (is_string($with)) {
            $with = explode(',', $with);
        }
        if (is_string($appends)) {
            $appends = explode(',', $appends);
        }
        if (is_string($exceptIds)) {
            $exceptIds = explode(',', $exceptIds);
        }

        return $this->getPaginator(
            with: $with,
            scopes: $scopes,
            orders: $orders,
            perPage: $perPage,
            appends: $appends,
            forcePagination: true,
            id: $request->get('id'),
            exceptIds: $exceptIds
        );
    }

    /**
     * Get paginated results with caching.
     * Automatically includes user context if user-aware traits are detected.
     * With relationship tracking enabled, caches are tagged with related model IDs
     * for granular invalidation.
     *
     * NOTE: This method caches using getCached() as the main entry point.
     * The get() method itself should NOT be wrapped in cacheable() to avoid
     * serialization issues with Laravel's paginator closures.
     *
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param int $perPage
     * @param array $appends
     * @param bool $forcePagination
     * @param int|string|null $id
     * @param array $exceptIds
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCached($with = [], $scopes = [], $orders = [], $perPage = 20, $appends = [], $forcePagination = false, $id = null, $exceptIds = [])
    {
        // $moduleName = $this->getCacheModuleName();
        // $routeName = $this->getCacheModuleRouteName();

        // $params = $this->addUserContext([
        //     'with' => $with,
        //     'scopes' => $scopes,
        //     'orders' => $orders,
        //     'perPage' => $perPage,
        //     'appends' => $appends,
        //     'forcePagination' => $forcePagination,
        //     'id' => $id,
        //     'exceptIds' => $exceptIds,
        //     'page' => request()->get('page', 1),
        // ]);

        // $cacheKey = $this->generateIndexCacheKey($params);
        // $ttl = $this->getCacheTtl('index');

        // Cache the serializable data instead of the paginator object
        $cachedData = $this->rememberCache(
            callback: function () use ($with, $scopes, $orders, $perPage, $appends, $forcePagination, $id, $exceptIds) {
                $result = $this->get($with, $scopes, $orders, $perPage, $appends, $forcePagination, $id, $exceptIds);

                // Convert paginator to serializable array
                if ($result instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                    return [
                        'type' => 'paginator',
                        'items' => $result->getCollection()->transform(function ($item) {
                            return $this->serializeModel($item);
                        }),
                        'total' => $result->total(),
                        'perPage' => $result->perPage(),
                        'currentPage' => $result->currentPage(),
                        'lastPage' => $result->lastPage(),
                    ];
                }

                // Return collection as-is (serializable)
                return [
                    'type' => 'collection',
                    'items' => $result->map(function ($item) {
                        return $this->serializeModel($item);
                    }),
                ];
            },
            type: 'index',
            data: [
                'with' => $with,
                'scopes' => $scopes,
                'orders' => $orders,
                'perPage' => $perPage,
                'appends' => $appends,
                'forcePagination' => $forcePagination,
                'id' => $id,
                'exceptIds' => $exceptIds,
                'page' => request()->get('page', 1),
                'locale' => app()->getLocale(),
            ],

        );

        // Reconstruct the paginator from cached data
        if ($cachedData['type'] === 'paginator') {
            return new LengthAwarePaginator(
                collect($cachedData['items'])->map(function ($item) {
                    return $this->unserializeModel($item);
                }),
                $cachedData['total'],
                $cachedData['perPage'],
                $cachedData['currentPage'],
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        return collect($cachedData['items'])->map(function ($item) {
            return $this->unserializeModel($item);
        });
    }

    /**
     * @param array $with
     * @param array $withCount
     * @param array $scopes
     * @return \Unusualify\Modularous\Entities\Model
     *
     * @throws ModelNotFoundException
     */
    public function getById($id, $with = [], $withCount = [], $lazy = [], $scopes = [], $useDefaultScopes = false)
    {
        $query = $this->model->query();

        // Apply scopes first (authorization/filtering)
        if (! empty($scopes) || $useDefaultScopes) {
            $query = $this->filter($query, $scopes);
        }

        $withs = $this->formatWiths($query, $with);

        if (classHasTrait($this->model, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            $query = $query->withTrashed();
        }

        $result = $query->with($withs)->withCount($withCount)->findOrFail($id);

        if ($lazy && count($lazy) > 0 && $result instanceof Model) {
            foreach ($lazy as $relation) {
                $parts = explode('.', $relation);

                if (count($parts) > 1) {
                    foreach ($parts as $i => $part) {
                        if ($i === 0) {
                            $result->load($part);
                        } else {
                            if ($result->{$parts[$i - 1]} instanceof Model) {
                                $result->{$parts[$i - 1]}->load($part);
                            } elseif ($result->{$parts[$i - 1]} instanceof \Illuminate\Database\Eloquent\Collection) {
                                $result->{$parts[$i - 1]} = $result->{$parts[$i - 1]}->map(function ($item) use ($part) {
                                    $item->{$part};

                                    return $item;
                                });
                            }
                        }
                    }
                } else {
                    $result->load($relation);
                }
            }
        }

        return $result;
    }

    /**
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param bool $isFormatted Deprecated. it's functionless now, be removed on v1.0.0
     * @param array $schema
     * @param array $lazy
     * @return Collection
     */
    public function getByIds(array $ids, $appends = [], $with = [], $scopes = [], $orders = [], $isFormatted = null, $schema = null, $lazy = [])
    {
        if ($isFormatted !== null) {
            if (function_exists('trigger_deprecation')) {
                trigger_deprecation(
                    'unusualify/modularous',
                    '0.53.0',
                    'The $isFormatted parameter is deprecated, functionless now, be removed on v1.0.0'
                );
            } else {
                // Fallback for older PHP versions
                @trigger_error(
                    'The $oldParam parameter is deprecated, use $newParam instead.',
                    E_USER_DEPRECATED
                );
            }
        }

        $query = $this->model->whereIn('id', $ids);

        $query = $query->with($this->formatWiths($query, $with));

        $query = $this->filter($query, $scopes);

        $query = $this->order($query, $orders);

        $result = $query->get();

        if ($lazy && count($lazy) > 0) {
            $result = $result->map(function ($item) use ($lazy) {
                if ($lazy && count($lazy) > 0 && $item instanceof Model) {
                    foreach ($lazy as $relation) {
                        $parts = explode('.', $relation);

                        if (count($parts) > 1) {
                            foreach ($parts as $i => $part) {
                                if ($i === 0) {
                                    $item->load($part);
                                } else {
                                    if ($item->{$parts[$i - 1]} instanceof Model) {
                                        $item->{$parts[$i - 1]}->load($part);
                                    } elseif ($item->{$parts[$i - 1]} instanceof \Illuminate\Database\Eloquent\Collection) {
                                        $item->{$parts[$i - 1]} = $item->{$parts[$i - 1]}->map(function ($item) use ($part) {
                                            $item->{$part};

                                            return $item;
                                        });
                                    }
                                }
                            }
                        } else {
                            $item->load($relation);
                        }
                    }
                }

                return $item;
            });
        }

        if ($appends && count($appends) > 0) {
            $result = $result->map(function ($item) use ($appends) {
                foreach ($appends as $append) {
                    $item->{$append} = $item->{$append};
                }

                return $item;
            });
        }

        return $result;
    }

    /**
     * @param string $column
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param bool $isFormatted
     * @param array $schema
     * @return Collection
     */
    public function getByColumnValue($column, array|string|int|bool $value, $with = [], $scopes = [], $orders = [], $isFormatted = false, $schema = null)
    {
        $query = is_array($value) ? $this->model->whereIn($column, $value) : $this->model->where($column, '=', $value);

        $query = $query->with($this->formatWiths($query, $with));

        $query = $this->filter($query, $scopes);

        $query = $this->order($query, $orders);

        if ($isFormatted) {
            return $query->get()->map(function ($item) {
                // dd($item);
                return array_merge(
                    $this->getShowFields($item, $this->chunkInputs($this->inputs())),
                    $item->attributesToArray(),
                    // $item->toArray(),
                    // $this->getFormFields($item, $this->chunkInputs($this->inputs())),
                    // $columnsData
                );
            });
        } else {
            return $query->get();
        }
    }

    /**
     * @param string $column
     * @param array $orders
     * @param null $exceptId
     * @return Collection
     */
    public function listAll($with = [], $scopes = [], $orders = [])
    {
        $query = $this->model->query();

        $query = $this->model->with($this->formatWiths($query, $with));

        if (isset($scopes['searches']) && isset($scopes['search']) && is_array($scopes['searches'])) {

            $this->searchIn($query, $scopes, 'search', $scopes['searches']);
            unset($scopes['searches']);
        }

        $query = $this->filter($query, $scopes);
        // $query = $this->filterBack($query, $scopes);

        $query = $this->order($query, $orders);

        return $query->get();
    }

    /**
     * @param string $column
     * @param array $orders
     * @param null $exceptId
     * @return Collection
     */
    public function list($column = 'name', $with = [], $scopes = [], $orders = [], $appends = [], $perPage = -1, $exceptId = null, $forcePagination = false)
    {
        $query = $this->model->newQuery();

        if (count($with) > 0) {
            $query = $query->with($this->formatWiths($query, $with));
        }

        if ($exceptId) {
            $query = $query->where($this->model->getTable() . '.id', '<>', $exceptId);
        }

        $query = $this->filter($query, $scopes);

        if (! empty($orders)) {
            $query = $this->order($query, $orders);
        } elseif ($this->model instanceof Sortable) {
            $query = $query->ordered();
        }

        $defaultColumns = is_array($column) ? $column : [$column];

        $columns = ['id', ...$defaultColumns];
        $oldColumns = $columns;

        $hasTableColumnCheck = method_exists($this->getModel(), 'getTableColumns');
        $tableColumns = [];
        if ($hasTableColumnCheck) {
            $tableColumns = $this->getModel()->getTableColumns();
        }

        $translatedColumns = [];

        if (method_exists($this->getModel(), 'isTranslatable') && $this->model->isTranslatable()) {
            $query = $query->withTranslation();
            $translatedAttributes = $this->getTranslatedAttributes();

            $columns = array_diff($columns, $translatedAttributes);
            $defaultColumns = array_diff($defaultColumns, $translatedAttributes);
            $translatedColumns = array_values(array_intersect($oldColumns, $translatedAttributes));

            if ($hasTableColumnCheck) {
                $absentColumns = array_diff($defaultColumns, $tableColumns);
                if (in_array('name', $absentColumns)) {
                    $titleColumnKey = $this->getModel()->getRouteTitleColumnKey();
                    if (in_array($titleColumnKey, $translatedAttributes)) {
                        $columns = array_filter($columns, fn ($col) => $col !== 'name');
                        $translatedColumns[] = $titleColumnKey;
                    } else {
                        $columns = array_filter($columns, fn ($col) => $col !== 'name');
                        $titleColumnKey = $this->getModel()->getRouteTitleColumnKey();
                        if (in_array($titleColumnKey, $tableColumns)) {
                            $columns[] = "{$titleColumnKey} as name";
                        }
                    }
                }
            }

        }

        $relationships = collect($with)->map(function ($r) {
            $r = explode('.', $r)[0];

            return $r;
        })->toArray();

        $foreignableRelationships = collect($relationships)->filter(function ($r) {
            return in_array($this->getModel()->getRelationType($r), ['BelongsTo', 'MorphTo']);
        })->values()->toArray();

        foreach ($foreignableRelationships as $r) {
            $columns[] = $this->getModel()->{$r}()->getForeignKeyName();
        }

        if (method_exists($this->getModel(), 'getWith')) {
            $with = array_values(array_unique(array_merge($this->getModel()->getWith(), $with)));
        }

        if ($hasTableColumnCheck && count($tableColumns) > 0) {
            $columns = array_values(array_unique(array_intersect($columns, $tableColumns)));
        }

        if (empty($columns)) {
            $columns = ['*'];
        }

        if ($forcePagination) {
            $paginator = $query->with($with)->paginate($perPage);

            $paginator->getCollection()->transform(fn ($item) => [
                ...collect($appends)->mapWithKeys(function ($append) use ($item) {
                    return [$append => $item->{$append}];
                })->toArray(),
                ...collect($with)->mapWithKeys(function ($r) use ($item) {
                    $r = explode('.', $r)[0];

                    return [$r => $item->{$r}];
                })->toArray(),
                ...(collect($columns)->mapWithKeys(fn ($column) => [$column => $item->{$column}])->toArray()),
                ...(collect($translatedColumns)->mapWithKeys(fn ($column) => [$column => $item->{$column}])->toArray()),
            ]);

            return $paginator;
        }

        return $query->with($with)->get($columns)->map(fn ($item) => [
            ...collect($appends)->mapWithKeys(function ($append) use ($item) {
                return [$append => $item->{$append}];
            })->toArray(),
            ...collect($with)->mapWithKeys(function ($r) use ($item) {
                $r = explode('.', $r)[0];

                return [$r => $item->{$r}];
            })->toArray(),
            ...(collect($columns)->mapWithKeys(fn ($column) => [$column => $item->{$column}])->toArray()),
            ...(collect($translatedColumns)->mapWithKeys(fn ($column) => [$column => $item->{$column}])->toArray()),
        ]);
    }

    /**
     * Post::with('user:id,username')->get();
     * Post::query()
     *   ->with(['user' => function ($query) {
     *      $query->select('id', 'username');
     *   }])
     *
     * @param Builder $query
     * @param array $with for instance ['roles' => ['select', 'id', 'name']]
     * @return array
     */
    public function formatWiths($query, $with)
    {
        return array_map(function ($item) {

            if (is_array($item)) {
                if (Arr::isAssoc($item)) {
                    return fn ($query) => array_reduce($item['functions'], fn ($query, $function) => $query->$function(), $query);
                }
            }

            return $item;
        }, $with);
    }

    /**
     * Get a single item with applied scopes for authorization
     *
     * @param mixed $id
     * @param array $with
     * @param array $withCount
     * @param array $lazy
     * @param array $scopes
     * @return \Unusualify\Modularous\Entities\Model
     *
     * @throws ModelNotFoundException
     *
     * @deprecated getByIdWithScopes is deprecated, use getById instead with $useDefaultScopes = true, will be removed on v1.0.0
     */
    public function getByIdWithScopes($id, $with = [], $withCount = [], $lazy = [], $scopes = [])
    {
        return $this->getById($id, $with, $withCount, $lazy, $scopes, useDefaultScopes: true);
    }
}
