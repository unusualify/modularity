<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Illuminate\Support\Arr;
use Unusualify\Modularity\Entities\Interfaces\Sortable;

trait QueryBuilder
{
    use MethodTransformers;

    /**
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param int $perPage
     * @param bool $forcePagination
     * @param int|string|null $id
     * @return \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function get($with = [], $scopes = [], $orders = [], $perPage = 20, $appends = [], $forcePagination = false, $id = null)
    {
        $query = $this->model->query();

        $query = $this->model->with($this->formatWiths($query, $with));

        if ($perPage === 0) {
            return $query->simplePaginate($perPage);
        }

        if (isset($scopes['searches']) && isset($scopes['search']) && is_array($scopes['searches'])) {
            $translatedAttributes = $this->model->translatedAttributes ?? [];

            // First, extract relationship fields (containing dots)
            $relationshipFields = [];
            $regularFields = [];

            foreach ($scopes['searches'] as $field) {
                if (strpos($field, '.') !== false) {
                    $relationshipFields[] = $field;
                } else {
                    $regularFields[] = $field;
                }
            }

            // Handle relationship field searching
            if (!empty($relationshipFields) && isset($scopes['search'])) {
                $this->searchInRelationships($query, $scopes, 'search', $relationshipFields);
            }

            // Remove translated attributes from regular fields for main table searching
            $searches = array_filter($regularFields, function ($field) use ($translatedAttributes) {
                return !in_array($field, $translatedAttributes);
            });

            // Search in main table fields (non-translated, non-relationship)
            $this->searchIn($query, $scopes, 'search', $searches);

            // Handle translated fields (existing logic)
            $scope['searches'] = array_filter($regularFields, function ($field) use ($translatedAttributes) {
                return in_array($field, $translatedAttributes);
            });
        }

        $query = $this->filter($query, $scopes);

        $query = $this->order($query, $orders);

        if (! $forcePagination && $this->model instanceof Sortable) {
            return $query->ordered()->paginate($perPage);

            return $query->ordered()->get();
        }

        $page = request()->get('page') ?? null;

        if ($id) {
            $totalRows = $query->count();
            // $totalPages = ceil($totalRows / $perPage);

            // Create a clone of the query to find the position of the record
            $cloneQuery = clone $query;
            // $orderColumns = $query->getQuery()->orders ?? [];

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
        }

        try {

            return $query->paginate($perPage, page: $page);

        } catch (\Throwable $th) {
            dd(
                $query->toSql(),
                $th,
                debug_backtrace()
            );
        }

    }

    /**
     * @param array $with
     * @param array $withCount
     * @param array $scopes
     * @return \Unusualify\Modularity\Models\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById($id, $with = [], $withCount = [], $lazy = [], $scopes = [])
    {
        $query = $this->model->query();

        // Apply scopes first (authorization/filtering)
        if (! empty($scopes)) {
            $query = $this->filter($query, $scopes);
        }

        $withs = $this->formatWiths($query, $with);

        if (classHasTrait($this->model, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            $result = $query->withTrashed()->with($withs)->withCount($withCount)->findOrFail($id);
        } else {
            $result = $query->with($withs)->withCount($withCount)->findOrFail($id);
        }

        if ($lazy && count($lazy) > 0 && $result instanceof \Illuminate\Database\Eloquent\Model) {
            foreach ($lazy as $relation) {
                $parts = explode('.', $relation);

                if (count($parts) > 1) {
                    foreach ($parts as $i => $part) {
                        if ($i === 0) {
                            $result = $result->load($part);
                        } else {
                            if ($result->{$parts[$i - 1]} instanceof \Illuminate\Database\Eloquent\Model) {
                                $result = $result->{$parts[$i - 1]}->load($part);
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
     * @param bool $isFormatted
     * @param array $schema
     * @return \Illuminate\Support\Collection
     */
    public function getByIds(array $ids, $with = [], $scopes = [], $orders = [], $isFormatted = false, $schema = null, $lazy = [])
    {
        $query = $this->model->whereIn('id', $ids);

        $query = $query->with($this->formatWiths($query, $with));

        $query = $this->filter($query, $scopes);

        $query = $this->order($query, $orders);

        if ($isFormatted) {
            return $query->get()->map(function ($item) use ($lazy) {

                if ($lazy && count($lazy) > 0 && $item instanceof \Illuminate\Database\Eloquent\Model) {
                    foreach ($lazy as $relation) {
                        $parts = explode('.', $relation);

                        if (count($parts) > 1) {
                            foreach ($parts as $i => $part) {
                                if ($i === 0) {
                                    $item = $item->load($part);
                                } else {
                                    if ($item->{$parts[$i - 1]} instanceof \Illuminate\Database\Eloquent\Model) {
                                        $item = $item->{$parts[$i - 1]}->load($part);
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

                return array_merge(
                    // array_merge(
                    //     $this->getShowFields($item, $this->chunkInputs($this->inputs())),
                    //     $this->getFormFields($item, $this->chunkInputs($this->inputs())),
                    // ),
                    $item->toArray(),
                );
            });
        } else {
            $result = $query->get();

            if ($lazy && count($lazy) > 0) {
                $result = $result->map(function ($item) use ($lazy) {
                    if ($lazy && count($lazy) > 0 && $item instanceof \Illuminate\Database\Eloquent\Model) {
                        foreach ($lazy as $relation) {
                            $parts = explode('.', $relation);

                            if (count($parts) > 1) {
                                foreach ($parts as $i => $part) {
                                    if ($i === 0) {
                                        $item = $item->load($part);
                                    } else {
                                        if ($item->{$parts[$i - 1]} instanceof \Illuminate\Database\Eloquent\Model) {
                                            $item = $item->{$parts[$i - 1]}->load($part);
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

            return $result;
        }
    }

    /**
     * @param string $column
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param bool $isFormatted
     * @param array $schema
     * @return \Illuminate\Support\Collection
     */
    public function getByColumnValues($column, array $values, $with = [], $scopes = [], $orders = [], $isFormatted = false, $schema = null)
    {
        $query = $this->model->whereIn($column, $values);

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
     * @return \Illuminate\Support\Collection
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

        try {
            return $query->get();
        } catch (\Throwable $th) {
            // throw $th;
            dd(
                $th,
                debug_backtrace()
            );
        }

    }

    /**
     * @param string $column
     * @param array $orders
     * @param null $exceptId
     * @return \Illuminate\Support\Collection
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

        if ($this->model instanceof Sortable) {
            $query = $query->ordered();
        } elseif (! empty($orders)) {
            $query = $this->order($query, $orders);
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

        try {
            if ($hasTableColumnCheck) {
                $columns = array_values(array_unique(array_intersect($columns, $tableColumns)));
            }
        } catch (\Throwable $th) {
            dd(
                $columns,
                $tableColumns,
                $this->getModel()->getColumns(),
                $this->getModel()
            );
        }

        try {
            // code...
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
        } catch (\Throwable $th) {
            dd(
                $this->getModel()->getRouteTitleColumnKey(),
                static::class,
                $columns,
                $appends,
                $with,
                $translatedColumns,
                $foreignableRelationships,
                $relationships,
                $th,
                array_reduce(debug_backtrace(), 'backtrace_formatter', [])
            );
        }

        // try {
        //     return $query->get($columns);
        // } catch (\Throwable $th) {
        //     if (method_exists($this->model, 'getColumns')) {
        //         $appends = $this->model->getAppends();
        //         $differentElements = array_diff($columns, $this->model->getColumns());
        //         // if absent columns exist in appends, we can return the result with the absent columns
        //         if (empty(array_diff($differentElements, $appends))) {
        //             // All differentElements exist in appends
        //             // You can proceed with your logic here if needed
        //             return $query->get()->map(fn ($item) => collect($columns)->map(fn ($c) => $item->{$c})->toArray());
        //         }
        //     }
        //     // no absent columns exist in appends, we can't return the result with the absent columns
        //     throw $th;
        // }

    }

    /**
     * Post::with('user:id,username')->get();
     * Post::query()
     *   ->with(['user' => function ($query) {
     *      $query->select('id', 'username');
     *   }])
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param array $with for instance ['roles' => ['select', 'id', 'name']]
     * @return array
     */
    public function formatWiths($query, $with)
    {
        return array_map(function ($item) {

            if (is_array($item)) {
                if (Arr::isAssoc($item)) {
                    return fn ($query) => array_reduce($item['functions'], fn ($query, $function) => $query->$function(), $query);
                } else {
                    if (request()->ajax()) {
                        // dd($item);
                    }
                }
            }

            return $item;

            return is_array($item)
                ? function ($query) use ($item) {

                    if (is_array($item[0])) {
                        foreach ($item as $key => $args) {
                            $query->{array_shift($args)}(...$args);
                        }
                    } else {
                        $query->{array_shift($item)}(...$item);
                    }
                    $query->without('pivot');
                }
            : $item;
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
     * @return \Unusualify\Modularity\Models\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByIdWithScopes($id, $with = [], $withCount = [], $lazy = [], $scopes = [])
    {
        $query = $this->model->query();

        // Apply scopes for authorization/filtering
        $query = $this->filter($query, $scopes);

        $withs = $this->formatWiths($query, $with);

        if (classHasTrait($this->model, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            $result = $query->withTrashed()->with($withs)->withCount($withCount)->findOrFail($id);
        } else {
            $result = $query->with($withs)->withCount($withCount)->findOrFail($id);
        }

        // Handle lazy loading
        if ($lazy && count($lazy) > 0 && $result instanceof \Illuminate\Database\Eloquent\Model) {
            foreach ($lazy as $relation) {
                $parts = explode('.', $relation);

                if (count($parts) > 1) {
                    foreach ($parts as $i => $part) {
                        if ($i === 0) {
                            $result = $result->load($part);
                        } else {
                            if ($result->{$parts[$i - 1]} instanceof \Illuminate\Database\Eloquent\Model) {
                                $result = $result->{$parts[$i - 1]}->load($part);
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
}
