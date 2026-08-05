<?php

namespace Unusualify\Modularous\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Modules\Cms\Http\Controllers\Traits\ResolvesPublicPresentationView;
use Modules\Cms\Support\CmsPageLayoutPresentationWrapper;
use Unusualify\Modularous\Services\MessageStage;

trait ManagePreview
{
    use ResolvesPublicPresentationView;

    protected function addMiddlewarePermissionsManagePreview()
    {
        if ($this->module && $this->routeHasTrait('revisions')) {
            $permissions = [
                'REVISION_RESTORE' => ['only' => ['restoreRevision']],
                'REVISION_APPROVE' => ['only' => ['approveRevision']],
                'REVISION_REJECT' => ['only' => ['rejectRevision']],
            ];

            foreach ($permissions as $permission => $options) {
                $this->setMiddlewarePermission($permission, $options);
            }
        }
    }

    public function previewData($item)
    {
        return [];
    }

    /**
     * Apply locale before {@see preview} / {@see previewForRevision} so hydration and Blade use the same language.
     * Query: activeLanguage (preferred) or locale.
     */
    protected function applyPreviewRequestLocale(): void
    {
        if ($this->request->filled('activeLanguage')) {
            App::setLocale((string) $this->request->get('activeLanguage'));

            return;
        }

        if ($this->request->filled('locale')) {
            App::setLocale((string) $this->request->get('locale'));
        }
    }

    public function showView($id)
    {
        $this->applyPreviewRequestLocale();

        if ($this->request->has('revisionId')) {
            $item = $this->repository->previewForRevision($id, $this->request->get('revisionId'), $this->formSchema);
        } else {
            $formRequest = $this->validateFormRequest();
            $item = $this->repository->preview($id, $formRequest->all());
        }

        $previewView = $this->presentationViewName();

        $innerData = array_replace([
            'item' => $item,
        ], $this->previewData($item));

        // Prefer PageLayout / filesystem page_layout shell from the CMR model; fall back to .custom.
        $wrapped = $item instanceof Model
            ? CmsPageLayoutPresentationWrapper::documentOrNull($item, $previewView, $innerData)
            : null;
        if ($wrapped !== null) {
            return view('cms::layout_builder.inline_document', ['document' => $wrapped]);
        }

        if (! View::exists($previewView)) {
            return View::make('twill::errors.preview', [
                'moduleName' => Str::singular($this->moduleName),
            ]);
        }

        return View::make($previewView, $innerData);
    }

    public function listRevisions($id)
    {
        if (! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $object = $this->repository->getModel()->newQuery()->findOrFail($id);

        return $object->revisionsArray();
    }

    public function restoreRevision($id)
    {
        if (! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $params = $this->request->route()->parameters();
        $id = last($params);
        $revisionId = (int) $this->request->get('revisionId');
        // dd($revisionId);

        if ($revisionId < 1) {
            return $this->respondWithError(__('Revision id is required.'));
        }

        if ($this->request->get('preview')) {
            // dd("preview is called for revision id: $revisionId");
            $rawPayload = $this->repository->getRevisionPayload((int) $id, $revisionId);

            return Response::json([
                'form_fields' => $rawPayload,
            ]);
        }

        $item = $this->repository->restoreRevision((int) $id, $revisionId);
        // dd($item);

        return Response::json([
            'message' => __('Revision restored successfully.'),
            'variant' => MessageStage::SUCCESS,
            'revisions' => $item->revisionsArray(),
            'form_fields' => $this->repository->getFormFields($item, $this->getPreviousRouteSchema()),
        ]);
    }

    public function approveRevision($id)
    {
        if (! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $params = $this->request->route()->parameters();
        $id = last($params);
        $revisionId = (int) $this->request->get('revisionId');

        if ($revisionId < 1) {
            return $this->respondWithError(__('Revision id is required.'));
        }

        $item = $this->repository->approveRevision((int) $id, $revisionId);

        return Response::json([
            'message' => __('messages.revision.approved-success'),
            'variant' => MessageStage::SUCCESS,
            'revisions' => $item->revisionsArray(),
            'form_fields' => $this->repository->getFormFields($item, $this->getPreviousRouteSchema()),
        ]);
    }

    public function rejectRevision($id)
    {
        if (! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $params = $this->request->route()->parameters();
        $id = last($params);
        $revisionId = (int) $this->request->get('revisionId');

        if ($revisionId < 1) {
            return $this->respondWithError(__('Revision id is required.'));
        }

        $item = $this->repository->rejectRevision((int) $id, $revisionId);

        return Response::json([
            'message' => __('messages.revision.rejected-success'),
            'variant' => MessageStage::SUCCESS,
            'revisions' => $item->revisionsArray(),
            'form_fields' => $this->repository->getFormFields($item, $this->getPreviousRouteSchema()),
        ]);
    }
}
