<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\ModularityLog;
use Unusualify\Modularity\Http\Controllers\Traits\ManageEvents;
use Unusualify\Modularity\Http\Controllers\Traits\ManagePrevious;
use Unusualify\Modularity\Http\Controllers\Traits\ManageSingleton;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Services\MessageStage;

abstract class BaseController extends PanelController
{
    use ManagePrevious, ManageUtilities, ManageEvents, ManageSingleton;

    /**
     * @var string
     */
    protected $viewPrefix;

    /**
     * Name of the index column to use as identifier column.
     *
     * @var string
     */
    protected $identifierColumnKey = 'id';

    /**
     * Attribute to use as title in forms.
     *
     * @var string
     */
    protected $titleFormKey;

    public function __construct(
        Application $app,
        Request $request
    ) {
        parent::__construct($app, $request);

        // $this->setMiddlewarePermission();
        $this->viewPrefix = $this->getViewPrefix();

        $this->__afterConstruct($app, $request);

    }

    public function index($parentId = null)
    {
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addIndexWiths();

        if ($this->request->ajax()) {
            if ($this->request->has('ids')) {
                $ids = $this->request->get('ids');

                if (is_string($ids)) {
                    $ids = explode(',', $ids);
                }

                $eagers = $this->request->get('eagers') ?? [];
                if (is_string($eagers)) {
                    $eagers = explode(',', $eagers);
                }

                $scopes = $this->request->get('scopes') ?? [];
                if (is_string($scopes)) {
                    $scopes = explode(',', $scopes);
                }

                $orders = $this->request->get('orders') ?? [];
                if (is_string($orders)) {
                    $orders = explode(',', $orders);
                }

                return Response::json(
                    $this->repository->getByIds(
                        ids: $ids,
                        with: $eagers,
                        scopes: $scopes,
                        orders: $orders,
                        isFormatted: false,
                    )
                );
            }

            $with = $this->request->get('eager', $this->request->get('with', []));

            return Response::json([
                'resource' => $this->getJSONData(with: $with),
                'mainFilters' => $this->getTableMainFilters($this->getExactScope()),
                // 'mainFilters' => $this->getTableMainFilters(),
                'replaceUrl' => $this->getReplaceUrl(),
            ]);
        }

        $indexData = $this->getIndexData($this->nestedParentScopes());

        if ($this->request->has('openCreate') && $this->request->get('openCreate')) {
            $indexData += ['openCreate' => true];
        }

        $view = Collection::make([
            "$this->viewPrefix.index",
            "$this->baseKey::" . $this->getSnakeCase($this->routeName) . '.index',
            "$this->baseKey::layouts.index",
            "$this->baseKey::layouts.index",
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $indexData);
    }

    /**
     * @param int $parentModuleId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create($parentModuleId = null)
    {
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addFormWiths();

        if (! $this->getIndexOption('skipCreateModal') && false) {
            return Redirect::to(moduleRoute(
                $this->routeName,
                $this->routePrefix,
                'index',
                ['openCreate' => true]
            ));
        }

        $parentModuleId = $this->getParentModuleIdFromRequest($this->request) ?? $parentModuleId;

        // $this->submodule = isset($parentModuleId);
        // $this->submoduleParentId = $parentModuleId;

        $view = Collection::make([
            "$this->viewPrefix.form",
            "$this->baseKey::$this->routeName.form",
            "$this->baseKey::layouts.form",
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $this->getFormData(null));
    }

    /**
     * @param int|null $parentModuleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($parentId = null)
    {
        // $parentId = $this->parentId ?? $parentId;

        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addFormWiths();

        $input = $this->validateFormRequest()->all();

        // $optionalParent = $parentId ? [$this->getParentModuleForeignKey() => $parentId] : [];
        $optionalParent = $this->nestedParentScopes();

        // if (isset($input['cmsSaveType']) && $input['cmsSaveType'] === 'cancel') {
        //     return $this->respondWithRedirect(moduleRoute(
        //         $this->moduleName,
        //         $this->routePrefix,
        //         'create'
        //     ));
        // }

        $item = $this->repository->create($input + $optionalParent, $this->getPreviousRouteSchema());

        activity()->performedOn($item)->log('created');

        // $this->handleActionEvent($item, __FUNCTION__);
        // $this->fireEvent($input);

        Session::put($this->routeName . '_retain', true);

        if (isset($input['cmsSaveType']) && Str::endsWith($input['cmsSaveType'], '-close')) {
            return $this->respondWithRedirect($this->getBackLink());
        }

        if (isset($input['cmsSaveType']) && Str::endsWith($input['cmsSaveType'], '-new')) {
            return $this->respondWithRedirect(moduleRoute($this->routeName,
                $this->routePrefix,
                'create'
            ));
        }

        if ($this->getTableAttribute('redirectAfterCreate', false)) {
            return $this->respondWithRedirect(moduleRoute($this->routeName,
                $this->generateRoutePrefix(noNested: true),
                'edit',
                [Str::snake($this->routeName) => $this->getItemIdentifier($item)]
            ), ['variant' => MessageStage::SUCCESS, 'forceRedirect' => true]);
        }

        $moduleName = $this->module->getSnakeName();
        $routeName = Str::snake($this->routeName);

        $storeSuccessTranslation = Collection::make([
            "$moduleName::messages.$routeName.store-success",
            "$moduleName::messages.$routeName.save-success",
            "$moduleName::messages.store-success",
            "$moduleName::messages.save-success",
            'messages.store-success',
            'messages.save-success',
        ])->first(function ($notation) {
            return Lang::has($notation);
        });

        $message = Lang::get($storeSuccessTranslation);

        return $this->request->ajax()
            ? $this->respondWithSuccess($message)
            : $this->respondWithRedirect(moduleRoute($this->routeName,
                $this->generateRoutePrefix(noNested: true),
                'edit',
                [Str::snake($this->routeName) => $this->getItemIdentifier($item)]
            ));
    }

    /**
     * @param Request $request
     * @param int|$id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addFormWiths();

        $id = last($params);

        $item = $this->repository->getById(
            $id,
            with: $this->request->get('eagers') ?? [],
            // $this->formWithCount
            lazy: $this->request->get('lazy') ?? [],
        );

        $data = array_merge(
            $item->attributesToArray(),
            $this->repository->getShowFields($item),
            // $this->repository->getFormFields($item, $this->formSchema),
        );

        if ($this->request->ajax()) {

            return Response::json($item->toArray());
            // return $data;
            // return $indexData + ['replaceUrl' => true];
        }

        // if ($this->getIndexOption('editInModal')) {
        //     return $this->request->ajax()
        //     ? Response::json($this->modalFormData($id))
        //     : Redirect::to(moduleRoute($this->routeName, $this->routePrefix, 'index'));
        // }

        $this->setBackLink();

        $view = Collection::make([
            "$this->viewPrefix.form",
            "$this->baseKey::$this->routeName.form",
            "$this->baseKey::layouts.form",
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $this->getFormData($id));

        // if ($this->getIndexOption('editInModal')) {
        //     return Redirect::to(moduleRoute($this->routeName, $this->routePrefix, 'index'));
        // }

        // return $this->redirectToForm($this->getParentModuleIdFromRequest($this->request) ?? $submoduleId ?? $id);
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id = null)
    {
        $params = $this->request->route()->parameters();

        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addFormWiths();

        $id = last($params);

        if ($this->getIndexOption('editInModal')) {
            return $this->request->ajax()
            ? Response::json($this->modalFormData($id))
            : Redirect::to(moduleRoute($this->routeName, $this->routePrefix, 'index'));
        }

        $this->setBackLink();

        $view = Collection::make([
            "$this->viewPrefix.form",
            "$this->baseKey::$this->routeName.form",
            "$this->baseKey::layouts.form",
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $this->getFormData($id));
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, $submoduleId = null)
    {
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addFormWiths();

        $params = $this->request->route()->parameters();

        $id = last($params);

        if ($this->isSingleton) {
            $item = $this->repository->getModel()->single();
        } else {
            $item = $this->repository->getById($id);
        }
        $input = $this->request->all();

        if (isset($input['cmsSaveType']) && $input['cmsSaveType'] === 'cancel') {
            return $this->respondWithRedirect(moduleRoute($this->routeName,
                $this->routePrefix,
                'edit',
                [Str::singular($this->moduleName) => $id]
            ));
        } else {
            $formRequest = $this->validateFormRequest();

            $this->repository->update($id, $formRequest->all(), $this->getPreviousRouteSchema());

            // $this->handleActionEvent($item, __FUNCTION__);

            // $this->fireEvent();

            if (isset($input['cmsSaveType'])) {
                if (Str::endsWith($input['cmsSaveType'], '-close')) {
                    return $this->respondWithRedirect($this->getBackLink());
                } elseif (Str::endsWith($input['cmsSaveType'], '-new')) {
                    if ($this->getIndexOption('skipCreateModal')) {
                        return $this->respondWithRedirect(moduleRoute($this->routeName,
                            $this->routePrefix,
                            'create'
                        ));
                    }

                    return $this->respondWithRedirect(moduleRoute($this->routeName,
                        $this->routePrefix,
                        'index',
                        ['openCreate' => true]
                    ));
                } elseif ($input['cmsSaveType'] === 'restore') {
                    Session::flash('status', modularityTrans("$this->baseKey::lang.publisher.restore-success"));

                    return $this->respondWithRedirect(moduleRoute($this->routeName,
                        $this->routePrefix,
                        'edit',
                        [Str::singular($this->moduleName) => $id]
                    ));
                }
            }

            $moduleName = $this->module->getSnakeName();
            $routeName = Str::snake($this->routeName);

            $updateSuccessTranslation = Collection::make([
                "$moduleName::messages.$routeName.update-success",
                "$moduleName::messages.$routeName.save-success",
                "$moduleName::messages.update-success",
                "$moduleName::messages.save-success",
                'messages.update-success',
                'messages.save-success',
            ])->first(function ($notation) {
                return Lang::has($notation);
            });

            $message = Lang::get($updateSuccessTranslation);

            if ($this->routeHasTrait('revisions')) {
                return Response::json([
                    'message' => $message,
                    'variant' => MessageStage::SUCCESS,
                    'revisions' => $item->revisionsArray(),
                ]);
            }

            if ($this->request->ajax()) {
                return $this->respondWithSuccess($message);
            }

            return redirect()->back();
        }
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $id = last($params);

        $item = $this->repository->getById($id);

        // $this->handleActionEvent($item, __FUNCTION__);

        $moduleName = $this->module->getSnakeName();
        $routeName = Str::snake($this->routeName);

        if ($this->repository->delete($id)) {
            // $this->fireEvent();
            // activity()->performedOn($item)->log('deleted');

            $deleteSuccessTranslation = Collection::make([
                "$moduleName::messages.$routeName.delete-success",
                "$moduleName::messages.delete-success",
                'listing.delete.success',
            ])->first(function ($notation) {
                return Lang::has($notation);
            });

            $message = Lang::get($deleteSuccessTranslation, ['modelTitle' => $this->modelTitle]);

            return $this->respondWithSuccess($message);
            // return $this->respondWithSuccess(___("$this->baseKey::lang.listing.delete.success", ['modelTitle' => $this->modelTitle]));
        }

        $deleteErrorTranslation = Collection::make([
            "$moduleName::messages.$routeName.delete-error",
            "$moduleName::messages.delete-error",
            'listing.delete.error',
        ])->first(function ($notation) {
            return Lang::has($notation);
        });

        return $this->respondWithError(Lang::get($deleteErrorTranslation, ['modelTitle' => $this->modelTitle]));
        // return $this->respondWithError(modularityTrans("$this->baseKey::lang.listing.delete.error", ['modelTitle' => $this->modelTitle]));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete()
    {
        $item = $this->repository->getById($this->request->get('id'));

        $moduleName = $this->module->getSnakeName();
        $routeName = Str::snake($this->routeName);

        if ($this->repository->forceDelete($this->request->get('id'))) {
            // $this->fireEvent();
            // $this->handleActionEvent($item, __FUNCTION__);

            $forceDeleteSuccessTranslation = Collection::make([
                "$moduleName::messages.$routeName.force-delete-success",
                "$moduleName::messages.force-delete-success",
            ])->first(function ($notation) {
                return Lang::has($notation);
            });

            $message = Lang::get($forceDeleteSuccessTranslation, ['modelTitle' => $this->modelTitle]);

            return $this->respondWithSuccess($message);
        }

        $forceDeleteErrorTranslation = Collection::make([
            "$moduleName::messages.$routeName.force-delete-error",
            "$moduleName::messages.force-delete-error",
            'listing.force-delete.error',
        ])->first(function ($notation) {
            return Lang::has($notation);
        });

        return $this->respondWithError(Lang::get($forceDeleteErrorTranslation, ['modelTitle' => $this->modelTitle]));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore()
    {

        if ($this->repository->restore($this->request->get('id'))) {
            // $this->fireEvent();
            activity()->performedOn($this->repository->getById($this->request->get('id')))->log('restored');
            // $this->handleActionEvent($this->repository->getById($this->request->get('id')), __FUNCTION__);

            return $this->respondWithSuccess(__('listing.restore.success', ['modelTitle' => $this->modelTitle]), attributes: ['location' => 'top']);
        }

        return $this->respondWithError(__('listing.restore.error', ['modelTitle' => $this->modelTitle]));
    }

    /**
     * @param Illuminate\Pagination\LengthAwarePaginator $paginator
     * @return array
     */
    public function getFormattedIndexItems($paginator) // getIndexTableItems
    {
        $translated = $this->routeHas('translations');

        $schema = $this->formSchema;

        $paginator->getCollection()->transform(function ($item) use ($translated, $schema) {
            $columnsData = Collection::make($this->getIndexTableColumns())->mapWithKeys(function (&$column) use ($item) {
                return $this->getItemColumnData($item, $column);
            })->toArray();

            // $name = $columnsData[$this->titleColumnKey] ?? $this->searchTitleKeyValue($columnsData);
            $name = data_get($item, $this->titleColumnKey, '');

            if (empty($name)) {
                if ($translated) {
                    $fallBackTranslation = $item->translations()->where('active', true)->first();

                    if (isset($fallBackTranslation->{$this->titleColumnKey})) {
                        $name = $fallBackTranslation->{$this->titleColumnKey};
                    }
                }

                $name = $name ?? ('Missing ' . $this->titleColumnKey);
            }

            unset($columnsData[$this->titleColumnKey]);

            $itemIsTrashed = method_exists($item, 'trashed') && $item->trashed();
            $itemCanDelete = $this->getIndexOption('delete') && ($item->canDelete ?? true);
            $canEdit = $this->getIndexOption('edit');
            $canDuplicate = $this->getIndexOption('duplicate');

            $itemId = $this->getItemIdentifier($item);

            $necessaryTableData = [
                'id' => $itemId,
                $this->titleColumnKey => $name,
                'deleted_at' => $item->deleted_at,
                // 'publish_start_date' => $item->publish_start_date,
                // 'publish_end_date' => $item->publish_end_date,
                // 'edit' => $canEdit ? $this->getModuleRoute($itemId, 'edit') : null,
                // 'duplicate' => $canDuplicate ? $this->getModuleRoute($itemId, 'duplicate') : null,
                // 'delete' => $itemCanDelete ? $this->getModuleRoute($itemId, 'destroy') : null,
            ];

            return array_replace(
                array_merge(
                    (($this->tableAttributes['editOnModal'] ?? true) ? $this->repository->getShowFields($item, $schema) : []),
                    // ($this->tableAttributes['editOnModal'] ?? true) ? $item->toArray() : ['id' => $itemId],
                    $item->toArray(),
                    $necessaryTableData,
                    (($this->tableAttributes['editOnModal'] ?? true) ? $this->repository->getFormFields($item, $schema) : []),
                    // $this->repository->getFormFields($item, $schema),
                    $columnsData,
                    $this->getCustomRowData($item),
                    // + ($this->getIndexOption('editInModal') ? [
                    //     'editInModal' => $this->getModuleRoute($itemId, 'edit'),
                    //     'updateUrl' => $this->getModuleRoute($itemId, 'update'),
                    // ] : [])
                    // + ($this->getIndexOption('publish') && ($item->canPublish ?? true) ? [
                    //     'published' => $item->published,
                    // ] : [])
                    // + ($this->getIndexOption('feature') && ($item->canFeature ?? true) ? [
                    //     'featured' => $item->{$this->featureField},
                    // ] : [])
                    // + (($this->getIndexOption('restore') && $itemIsTrashed) ? [
                    //     'deleted' => true,
                    // ] : [])
                    // + (($this->getIndexOption('forceDelete') && $itemIsTrashed) ? [
                    //     'destroyable' => true,
                    // ] : [])
                    // + ($translated ? [
                    //     'languages' => $item->getActiveLanguages(),
                    // ] : [])

                ), $this->indexItemData($item)
            );
        });

        return $paginator->toArray();
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @param array $column
     * @return array
     */
    protected function getItemColumnData($item, $column)
    {

        if (isset($column['thumb']) && $column['thumb']) {
            if (isset($column['present']) && $column['present']) {
                return [
                    'thumbnail' => $item->presentAdmin()->{$column['presenter']},
                ];
            } else {
                $variant = isset($column['variant']);
                $role = $variant ? $column['variant']['role'] : head(array_keys($item->mediasParams));
                $crop = $variant ? $column['variant']['crop'] : head(array_keys(head($item->mediasParams)));
                $params = $variant && isset($column['variant']['params'])
                ? $column['variant']['params']
                : ['w' => 80, 'h' => 80, 'fit' => 'crop'];

                return [
                    'thumbnail' => $item->cmsImage($role, $crop, $params),
                ];
            }
        }

        if (isset($column['nested']) && $column['nested']) {
            $field = $column['nested'];
            $nestedCount = $item->{$column['nested']}->count();
            $module = Str::singular(last(explode('.', $this->moduleName)));
            $value = '<a href="';
            $value .= moduleRoute("$this->moduleName.$field", $this->routePrefix, 'index', [$module => $this->getItemIdentifier($item)]);
            $value .= '">' . $nestedCount . ' ' . (mb_strtolower(Str::plural($column['title'], $nestedCount))) . '</a>';
        } else {
            $field = $column['key'];
            $value = data_get($item, $field, null);
        }

        // for relationship fields
        if (preg_match('/(.*)(_relation)/', $column['key'], $matches)) {
            // $field = $column['key'];
            $relationshipName = $matches[1];
            $exploded = explode('.', $relationshipName);

            $relation = null;
            if (count($exploded) > 1) {
                $relationshipName = $exploded[1];
                $item = $item->{$exploded[0]};
            } else {
                $relation = $item->{$relationshipName}();
            }

            $itemTitle = $column['itemTitle'] ?? 'name';
            $maxItems = $column['maxItems'] ?? 3;

            $count = 0;

            try {
                $relationshipType = get_class($item->{$relationshipName}());

                if (in_array($relationshipType, [
                    'Illuminate\Database\Eloquent\Relations\BelongsTo',
                    'Illuminate\Database\Eloquent\Relations\HasOne',
                    'Illuminate\Database\Eloquent\Relations\HasOneThrough',
                    'Illuminate\Database\Eloquent\Relations\MorphOne',
                    'Illuminate\Database\Eloquent\Relations\MorphTo',
                ])) {
                    $result = $item->{$relationshipName};
                } elseif (in_array($relationshipType, [
                    'Illuminate\Database\Eloquent\Relations\BelongsToMany',
                    'Illuminate\Database\Eloquent\Relations\HasMany',
                    'Illuminate\Database\Eloquent\Relations\HasManyThrough',
                    'Illuminate\Database\Eloquent\Relations\MorphMany',
                    'Illuminate\Database\Eloquent\Relations\MorphToMany',
                ])) {
                    $count = $item->{$relationshipName}()->count();
                    $result = $item->{$relationshipName}()
                        ->take($maxItems)
                        ->get();
                } else {
                    $result = $item->{$relationshipName};
                }

                if ($result instanceof Collection) {
                    $value = $result
                        ->pluck($itemTitle)
                        ->join(', ');

                    if ($count > $maxItems) {
                        $value .= ' ...';
                    }
                } elseif ($result instanceof Model) {
                    $value = $result->{$itemTitle};
                } else {
                    $value = $result;
                }
            } catch (\Throwable $th) {
                ModularityLog::error('Error getting item column data', [
                    'relationshipName' => $relationshipName,
                    'result' => $result,
                    'item' => $item,
                    'th' => $th,
                ]);
            }
        }

        if (preg_match('/(.*)(_timestamp)/', $column['key'], $matches)) {
            $value = $item->{$matches[1]};
        }

        if (preg_match('/(.*)(_uuid)/', $column['key'], $matches)) {
            // $value = $item->{$matches[1]};
            // $value = mb_substr($item->{$matches[1]}, 0, 6);
            $value = $item->{$matches[1]};
            // $value = "<span>" . substr($item->{$matches[1]}, 0, 6) . "</span>";
        }

        if (isset($column['relationship'])) {
            $field = $column['relationship'] . ucfirst($column['field']);

            $relation = $item->{$column['relationship']}();

            $value = collect($relation->get())
                ->pluck($column['field'])
                ->join(', ');

        } elseif (isset($column['present']) && $column['present']) {
            $value = $item->presentAdmin()->{$column['field']};
        }

        if (isset($column['relatedBrowser']) && $column['relatedBrowser']) {
            $field = 'relatedBrowser' . ucfirst($column['relatedBrowser']) . ucfirst($column['field']);
            $value = $item->getRelated($column['relatedBrowser'])
                ->pluck($column['field'])
                ->join(', ');
        }

        if (is_array($value)
            && (isset($value['title']) || isset($value['name']))
        ) {
            $value = $value['title'] ?? $value['name'];
        }

        return [
            "$field" => $value,
        ];
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @return array
     */
    protected function getCustomRowData($item)
    {
        $customRows = $this->tableAttributes['customRow'] ?? [];
        $customRowFillable = [];

        foreach ($customRows as $customRow) {
            if (isset($customRow['allowedRoles']) && $this->user->hasRole($customRow['allowedRoles'])) {
                if ($customRow['itemAttributes'] && is_array($customRow['itemAttributes'])) {
                    $customRowFillable = $customRow['itemAttributes'];
                } else {
                    $customRowFillable = [];
                }

                break;
            }
        }

        $customRowData = [];

        foreach ($customRowFillable as $fillable) {
            $customRowData[$fillable] = $item->{$fillable};
        }

        return $customRowData;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return array
     */
    protected function _getIndexTableColumns($items)
    {
        $tableColumns = [];
        $visibleColumns = $this->request->get('columns') ?? false;
        $indexColumnCopy = $this->indexColumns;

        if (isset(Arr::first($indexColumnCopy)['thumb'])
            && Arr::first($indexColumnCopy)['thumb']
        ) {
            $tableColumns[] = [
                'name' => 'thumbnail',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.thumbnail"),
                'visible' => $visibleColumns ? in_array('thumbnail', $visibleColumns) : true,
                'optional' => true,
                'sortable' => false,
            ];
            array_shift($indexColumnCopy);
        }

        if ($this->getIndexOption('feature')) {
            $tableColumns[] = [
                'name' => 'featured',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.featured"),
                'visible' => true,
                'optional' => false,
                'sortable' => false,
            ];
        }
        if ($this->getIndexOption('publish')) {
            $tableColumns[] = [
                'name' => 'published',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.published"),
                'visible' => true,
                'optional' => false,
                'sortable' => false,
            ];
        }

        $tableColumns[] = [
            'name' => 'name',
            'label' => $indexColumnCopy[$this->titleColumnKey]['title'] ?? modularityTrans("$this->baseKey::lang.listing.columns.name"),
            'visible' => true,
            'optional' => false,
            'sortable' => $this->getIndexOption('reorder') ? false : ($indexColumnCopy[$this->titleColumnKey]['sort'] ?? false),
        ];

        unset($indexColumnCopy[$this->titleColumnKey]);

        foreach ($indexColumnCopy as $column) {
            if (isset($column['relationship'])) {
                $columnName = $column['relationship'] . ucfirst($column['field']);
            } elseif (isset($column['nested'])) {
                $columnName = $column['nested'];
            } elseif (isset($column['relatedBrowser'])) {
                $columnName = 'relatedBrowser' . ucfirst($column['relatedBrowser']) . ucfirst($column['field']);
            } else {
                $columnName = $column['value'];
                // $columnName = $column['field'];
            }

            $tableColumns[] = [
                'name' => $columnName,
                'label' => $column['text'],
                // 'label' => $column['title'],
                'visible' => $visibleColumns ? in_array($columnName, $visibleColumns) : ($column['visible'] ?? true),
                'optional' => $column['optional'] ?? true,
                'sortable' => $this->getIndexOption('reorder') ? false : ($column['sort'] ?? false),
                'html' => $column['html'] ?? false,
            ];
        }

        if ($this->getIndexOption('includeScheduledInList') && $this->repository->isFillable('publish_start_date')) {
            $tableColumns[] = [
                'name' => 'publish_start_date',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.published"),
                'visible' => true,
                'optional' => true,
                'sortable' => true,
            ];
        }

        if ($this->routeHasTrait('translations') && count(getLocales()) > 1) {
            $tableColumns[] = [
                'name' => 'languages',
                'label' => modularityTrans("$this->baseKey::lang.listing.languages"),
                'visible' => $visibleColumns ? in_array('languages', $visibleColumns) : true,
                'optional' => true,
                'sortable' => false,
            ];
        }

        return $tableColumns;
    }

    protected function getViewPrefix(): ?string
    {
        $module_prefix = Str::snake($this->moduleName);

        $route_prefix = Str::snake($this->routeName);

        // dd($module_prefix, $route_prefix);

        return "$module_prefix::$route_prefix";

        $prefix = "admin.$this->moduleName";

        if (view()->exists("$prefix.form")) {
            return $prefix;
        }

        // try {
        //     return TwillCapsules::getCapsuleForModel($this->modelName)->getViewPrefix();
        // } catch (NoCapsuleFoundException $e) {
        //     return null;
        // }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @return int|string
     */
    protected function getItemIdentifier($item)
    {
        return $item->{$this->identifierColumnKey};
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function duplicate($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));

        $this->addWiths();

        $this->addFormWiths();

        $id = last($params);

        $item = $this->repository->getById($id);

        if ($newItem = $this->repository->duplicate($id, $this->titleColumnKey, $this->formSchema)) {
            // $this->fireEvent();
            activity()->performedOn($item)->log('duplicated');

            return Response::json([
                'message' => __('listing.duplicate.success', ['modelTitle' => $this->modelTitle]),
                'variant' => MessageStage::SUCCESS,
                'target' => '_blank',
                'redirector' => moduleRoute(
                    $this->routeName,
                    $this->routePrefix,
                    'edit',
                    array_filter([snakeCase($this->routeName) => $newItem->id])
                ),
            ]);
        }

        return $this->respondWithError(__('listing.duplicate.error', ['modelTitle' => $this->modelTitle]));
    }

    public function searchTitleKeyValue($columnsData)
    {
        $value = null;

        if (isset($columnsData[($newKey = $this->titleColumnKey . '_relation')])) {
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        } elseif (isset($columnsData[($newKey = $this->titleColumnKey . '_timestamp')])) {
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        } elseif (isset($columnsData[($newKey = $this->titleColumnKey . '_uuid')])) {
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        } else {
            $newKey = array_keys($columnsData)[0];
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        }

        return $value;
    }

    public function bulkDelete()
    {
        $ids = is_array($this->request->get('ids')) ? $this->request->get('ids') : explode(',', $this->request->get('ids'));

        if ($this->repository->bulkDelete($ids)) {
            return $this->respondWithSuccess(___('listing.bulk-delete.success', ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(___('listing.bulk-delete.error', ['modelTitle' => $this->modelTitle]));

    }

    public function bulkForceDelete()
    {
        $ids = is_array($this->request->get('ids')) ? $this->request->get('ids') : explode(',', $this->request->get('ids'));

        if ($this->repository->bulkForceDelete($ids)) {
            return $this->respondWithSuccess(___('listing.bulk-force-delete.success', ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(___('listing.bulk-force-delete.error', ['modelTitle' => $this->modelTitle]));
    }

    public function bulkRestore()
    {
        $ids = is_array($this->request->get('ids')) ? $this->request->get('ids') : explode(',', $this->request->get('ids'));

        if ($this->repository->bulkRestore($ids)) {
            return $this->respondWithSuccess(___('listing.bulk-restore.success', ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(___('listing.bulk-restore.error', ['modelTitle' => $this->modelTitle]));
    }

    public function reorder()
    {
        $ids = is_array($this->request->get('ids')) ? $this->request->get('ids') : explode(',', $this->request->get('ids'));

        if ($this->repository->getModel()->setNewOrder($ids)) {

            return $this->respondWithSuccess(___('listing.reorder.success', ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(___('listing.reorder.error', ['modelTitle' => $this->modelTitle]));
    }
}
