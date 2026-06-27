<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Modules\Cms\Http\Controllers\Traits\ManageCms;
use Unusualify\Modularous\Http\Controllers\Traits\ManageBulkSheet;
use Unusualify\Modularous\Http\Controllers\Traits\ManageIndexAjax;
use Unusualify\Modularous\Http\Controllers\Traits\ManageInertia;
use Unusualify\Modularous\Http\Controllers\Traits\ManagePreview;
use Unusualify\Modularous\Http\Controllers\Traits\ManagePrevious;
use Unusualify\Modularous\Http\Controllers\Traits\ManageRemoteApiSync;
use Unusualify\Modularous\Http\Controllers\Traits\ManageSingleton;
use Unusualify\Modularous\Http\Controllers\Traits\ManageTranslations;
use Unusualify\Modularous\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularous\Services\MessageStage;

abstract class BaseController extends PanelController
{
    use ManageBulkSheet,
        ManageIndexAjax,
        ManagePrevious,
        ManageRemoteApiSync,
        ManageUtilities,
        ManageSingleton,
        ManageInertia,
        ManageTranslations,
        ManagePreview,
        ManageCms;

    /**
     * @var string
     */
    protected $viewPrefix;

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

    protected function getViewPrefix(): ?string
    {
        $prefix = $this->presentationViewPrefix();

        return $prefix !== '' ? $prefix : null;
    }

    public function preload()
    {
        parent::preload();

        $this->addWiths();

        $this->setupFormSchema();
    }

    public function index($parentId = null)
    {
        $this->addWiths();
        $this->addIndexWiths();

        $tableEditOnModal = $this->tableAttributes['editOnModal'] ?? true;
        if ($tableEditOnModal) {
            $this->addFormWiths();
        }

        $ajaxResponse = $this->respondToIndexAjax();
        if ($ajaxResponse !== null) {
            return $ajaxResponse;
        }

        $indexData = $this->getIndexData($this->nestedParentScopes());

        if ($this->request->has('openCreate') && $this->request->get('openCreate')) {
            $indexData += ['openCreate' => true];
        }

        return $this->renderIndex($indexData);
    }

    /**
     * @param int $parentModuleId
     * @return JsonResponse|RedirectResponse|\Illuminate\View\View
     */
    public function create($parentModuleId = null)
    {
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

        return $this->renderForm($this->getFormData(null));
    }

    /**
     * @param int|null $parentModuleId
     * @return JsonResponse
     */
    public function store($parentId = null)
    {
        // $parentId = $this->parentId ?? $parentId;

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

        $message = $this->getTranslationFromKeys([
            "$moduleName::messages.$routeName.store-success",
            "$moduleName::messages.$routeName.save-success",
            "modules.$moduleName.$routeName.messages.store-success",
            "modules.$moduleName.$routeName.messages.save-success",
            "$moduleName::messages.store-success",
            "$moduleName::messages.save-success",
            'messages.store-success',
            'messages.save-success',
        ]);

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
     * @return RedirectResponse
     */
    public function show($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

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
     * @return JsonResponse|RedirectResponse|\Illuminate\View\View
     */
    public function edit($id = null)
    {
        $params = $this->request->route()->parameters();

        $this->addWiths();

        $this->addFormWiths();

        $id = last($params);

        if ($this->getIndexOption('editInModal')) {
            return $this->request->ajax()
            ? Response::json($this->modalFormData($id))
            : Redirect::to(moduleRoute($this->routeName, $this->routePrefix, 'index'));
        }

        $this->setBackLink();

        return $this->renderForm($this->getFormData($id));
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return JsonResponse
     */
    public function update($id, $submoduleId = null)
    {
        $this->addWiths();

        $this->addFormWiths();

        $params = $this->request->route()->parameters();

        $id = last($params);

        if ($this->isSingleton) {
            $item = $this->repository->getModel()->single();
            $id = $this->getItemIdentifier($item);
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
            $item = $this->repository->getById($id);

            // $this->handleActionEvent($item, __FUNCTION__);

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
                    Session::flash('status', modularousTrans("$this->baseKey::lang.publisher.restore-success"));

                    return $this->respondWithRedirect(moduleRoute($this->routeName,
                        $this->routePrefix,
                        'edit',
                        [Str::singular($this->moduleName) => $id]
                    ));
                }
            }

            $moduleName = $this->module->getSnakeName();
            $routeName = Str::snake($this->routeName);

            $message = $this->getTranslationFromKeys([
                "$moduleName::messages.$routeName.update-success",
                "$moduleName::messages.$routeName.save-success",
                "modules.$moduleName.$routeName.messages.update-success",
                "modules.$moduleName.$routeName.messages.save-success",
                "$moduleName::messages.update-success",
                "$moduleName::messages.save-success",
                'messages.update-success',
                'messages.save-success',
            ]);

            if ($this->routeHasTrait('revisions')) {
                return $this->respondWithSuccess($message, [
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
     * @return JsonResponse
     */
    public function destroy($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $id = last($params);

        $item = $this->repository->getById($id);

        $moduleName = $this->module->getSnakeName();
        $routeName = Str::snake($this->routeName);

        if ($this->repository->delete($id)) {
            // activity()->performedOn($item)->log('deleted');

            $message = $this->getTranslationFromKeys([
                "$moduleName::messages.$routeName.delete-success",
                "modules.$moduleName.$routeName.messages.delete-success",
                "$moduleName::messages.delete-success",
                'listing.delete.success',
            ], ['modelTitle' => $this->modelTitle]);

            return $this->respondWithSuccess($message);
            // return $this->respondWithSuccess(___("$this->baseKey::lang.listing.delete.success", ['modelTitle' => $this->modelTitle]));
        }

        $message = $this->getTranslationFromKeys([
            "$moduleName::messages.$routeName.delete-error",
            "modules.$moduleName.$routeName.messages.delete-error",
            "$moduleName::messages.delete-error",
            'listing.delete.error',
        ], ['modelTitle' => $this->modelTitle]);

        return $this->respondWithError($message);
        // return $this->respondWithError(modularousTrans("$this->baseKey::lang.listing.delete.error", ['modelTitle' => $this->modelTitle]));
    }

    /**
     * @return JsonResponse
     */
    public function forceDelete()
    {
        $item = $this->repository->getById($this->request->get('id'));

        $moduleName = $this->module->getSnakeName();
        $routeName = Str::snake($this->routeName);

        if ($this->repository->forceDelete($this->request->get('id'))) {
            $message = $this->getTranslationFromKeys([
                "$moduleName::messages.$routeName.force-delete-success",
                "modules.$moduleName.$routeName.messages.force-delete-success",
                "$moduleName::messages.force-delete-success",
            ], ['modelTitle' => $this->modelTitle]);

            return $this->respondWithSuccess($message);
        }

        $message = $this->getTranslationFromKeys([
            "$moduleName::messages.$routeName.force-delete-error",
            "modules.$moduleName.$routeName.messages.force-delete-error",
            "$moduleName::messages.force-delete-error",
            'listing.force-delete.error',
        ], ['modelTitle' => $this->modelTitle]);

        return $this->respondWithError($message);
    }

    /**
     * @return JsonResponse
     */
    public function restore()
    {
        $moduleName = $this->module->getSnakeName();
        $routeName = Str::snake($this->routeName);

        if ($this->repository->restore($this->request->get('id'))) {
            activity()->performedOn($this->repository->getById($this->request->get('id')))->log('restored');

            $message = $this->getTranslationFromKeys([
                "modules.$moduleName.$routeName.messages.restore-success",
                "$moduleName::messages.restore-success",
                'listing.restore.success',
            ], ['modelTitle' => $this->modelTitle]);

            return $this->respondWithSuccess($message, attributes: ['location' => 'top']);
        }

        return $this->respondWithError(__('listing.restore.error', ['modelTitle' => $this->modelTitle]));
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return JsonResponse
     */
    public function duplicate($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $this->preload();

        $this->addWiths();

        $this->addFormWiths();

        $id = last($params);

        $item = $this->repository->getById($id);

        if ($newItem = $this->repository->duplicate($id, $this->titleColumnKey, $this->formSchema)) {
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
