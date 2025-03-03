<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Http\Requests\MediaRequest;
use Unusualify\Modularity\Models\Media;
use Unusualify\Modularity\Services\Uploader\SignAzureUpload;
use Unusualify\Modularity\Services\Uploader\SignS3Upload;
use Unusualify\Modularity\Services\Uploader\SignUploadListener;

class MediaLibraryController extends BaseController implements SignUploadListener
{
    /**
     * @var string
     */
    protected $moduleName = 'Media';

    /**
     * @var string
     */
    protected $namespace = 'Unusualify\Modularity';

    /**
     * @var array
     */
    protected $defaultOrders = [
        'id' => 'desc',
    ];

    /**
     * @var array
     */
    protected $defaultFilters = [
        'search' => 'search',
        'tag' => 'tag_id',
        'unused' => 'unused',
    ];

    /**
     * @var int
     */
    protected $perPage = 40;

    /**
     * @var string
     */
    protected $endpointType;

    /**
     * @var array
     */
    protected $customFields;

    /**
     * @var Illuminate\Routing\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    protected $setDefaultPermissions = false;

    public function __construct(
        Application $app,
        Config $config,
        Request $request,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($app, $request);
        $this->responseFactory = $responseFactory;
        $this->config = $config;

        // $this->removeMiddleware('can:edit');
        // $this->removeMiddleware('can:_create');
        // $this->middleware('can:edit', ['only' => ['signS3Upload', 'signAzureUpload', 'tags', 'store', 'singleUpdate', 'bulkUpdate']]);
        $this->endpointType = $this->config->get(modularityBaseKey() . '.media_library.endpoint_type');
        $this->customFields = $this->config->get(modularityBaseKey() . '.media_library.extra_metadatas_fields');
    }

    /**
     * @param int|null $parentModuleId
     * @return array
     */
    public function index($parentModuleId = null)
    {
        if ($this->request->has('except')) {
            $prependScope['exceptIds'] = $this->request->get('except');
        }

        return $this->getIndexData($prependScope ?? []);
    }

    /**
     * @param array $prependScope
     * @return array
     */
    public function getIndexData($prependScope = [])
    {

        $scopes = $this->filterScope($prependScope);
        $items = $this->getIndexItems(scopes: $scopes, forcePagination: false);

        return [
            'items' => $items->map(function ($item) {
                return $item->mediableFormat();
            })->toArray(),
            'maxPage' => $items->lastPage(),
            'total' => $items->total(),
            'tags' => $this->repository->getTagsList(),
        ];
    }

    /**
     * @return array
     */
    protected function getRequestFilters()
    {
        if ($this->request->has('search')) {
            $requestFilters['search'] = $this->request->get('search');
        }

        if ($this->request->has('tag')) {
            $requestFilters['tag'] = $this->request->get('tag');
        }

        if ($this->request->has('unused') && (int) $this->request->unused === 1) {
            $requestFilters['unused'] = $this->request->get('unused');
        }

        return $requestFilters ?? [];
    }

    /**
     * @param int|null $parentModuleId
     */
    public function store($parentModuleId = null)
    {
        $request = $this->app->make(MediaRequest::class);
        if ($this->endpointType === 'local') {
            $media = $this->storeFile($request);
        } else {
            $media = $this->storeReference($request);
        }

        return $this->responseFactory->json(['media' => $media->mediableFormat(), 'success' => true], 200);
    }

    /**
     * @param Request $request
     * @return Media
     */
    public function storeFile($request)
    {
        $originalFilename = $request->input('qqfilename');

        $filename = sanitizeFilename($originalFilename);

        // dd($filename);

        $fileDirectory = $request->input('unique_folder_name');

        $uuid = $request->input('unique_folder_name') . '/' . $filename;

        if ($this->config->get(modularityBaseKey() . '.media_library.prefix_uuid_with_local_path', false)) {
            $prefix = trim($this->config->get(modularityBaseKey() . '.media_library.local_path'), '/ ') . '/';
            $fileDirectory = $prefix . $fileDirectory;
            $uuid = $prefix . $uuid;
        }

        $disk = $this->config->get(modularityBaseKey() . '.media_library.disk');

        $uploadedFile = $request->file('qqfile');

        [$w, $h] = getimagesize($uploadedFile->path());

        $uploadedFile->storeAs($fileDirectory, $filename, ['disk' => $disk]);

        $fields = [
            'uuid' => $uuid,
            'filename' => $originalFilename,
            'width' => $w,
            'height' => $h,
        ];

        if ($this->shouldReplaceMedia($id = $request->input('media_to_replace_id'))) {
            $media = $this->repository->whereId($id)->first();
            $this->repository->afterDelete($media);
            $media->replace($fields);

            return $media->fresh();
        } else {
            return $this->repository->create($fields);
        }
    }

    /**
     * @param Request $request
     */
    public function storeReference($request)
    {
        $fields = [
            'uuid' => $request->input('key') ?? $request->input('blob'),
            'filename' => $request->input('name'),
            'width' => $request->input('width'),
            'height' => $request->input('height'),
        ];

        if ($this->shouldReplaceMedia($id = $request->input('media_to_replace_id'))) {
            $media = $this->repository->whereId($id)->first();
            $this->repository->afterDelete($media);
            $media->update($fields);

            return $media->fresh();
        } else {
            return $this->repository->create($fields);
        }
    }

    /**
     * @return JsonResponse
     */
    public function singleUpdate()
    {
        $this->repository->update(
            $this->request->input('id'),
            array_merge([
                'alt_text' => $this->request->get('alt_text', null),
                'caption' => $this->request->get('caption', null),
                'tags' => $this->request->get('tags', null),
            ], $this->getExtraMetadatas()->toArray())
        );

        return $this->responseFactory->json([
            'tags' => $this->repository->getTagsList(),
        ], 200);
    }

    /**
     * @return JsonResponse
     */
    public function bulkUpdate()
    {
        $ids = explode(',', $this->request->input('ids'));

        $metadatasFromRequest = $this->getExtraMetadatas()->reject(function ($meta) {
            return is_null($meta);
        })->toArray();

        $extraMetadatas = array_diff_key($metadatasFromRequest, array_flip((array) $this->request->get('fieldsRemovedFromBulkEditing', [])));

        if (in_array('tags', $this->request->get('fieldsRemovedFromBulkEditing', []))) {
            $this->repository->addIgnoreFieldsBeforeSave('bulk_tags');
        } else {
            $previousCommonTags = $this->repository->getTags(null, $ids);
            $newTags = array_filter(explode(',', $this->request->input('tags')));
        }

        foreach ($ids as $id) {
            $this->repository->update($id, [
                'bulk_tags' => $newTags ?? [],
                'previous_common_tags' => $previousCommonTags ?? [],
            ] + $extraMetadatas);
        }

        $scopes = $this->filterScope(['id' => $ids]);
        $items = $this->getIndexItems(scopes: $scopes, forcePagination: false);

        return $this->responseFactory->json([
            'items' => $items->map(function ($item) {
                return $item->mediableFormat();
            })->toArray(),
            'tags' => $this->repository->getTagsList(),
        ], 200);
    }

    /**
     * @return mixed
     */
    public function signS3Upload(Request $request, SignS3Upload $signS3Upload)
    {
        return $signS3Upload->fromPolicy($request->getContent(), $this, $this->config->get(modularityBaseKey() . '.media_library.disk'));
    }

    /**
     * @return mixed
     */
    public function signAzureUpload(Request $request, SignAzureUpload $signAzureUpload)
    {
        return $signAzureUpload->getSasUrl($request, $this, $this->config->get(modularityBaseKey() . '.media_library.disk'));
    }

    /**
     * @param bool $isJsonResponse
     * @return mixed
     */
    public function uploadIsSigned($signature, $isJsonResponse = true)
    {
        return $isJsonResponse
        ? $this->responseFactory->json($signature, 200)
        : $this->responseFactory->make($signature, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * @return JsonResponse
     */
    public function uploadIsNotValid()
    {
        return $this->responseFactory->json(['invalid' => true], 500);
    }

    /**
     * @return Collection
     */
    private function getExtraMetadatas()
    {
        return Collection::make($this->customFields)->mapWithKeys(function ($field) {
            $fieldInRequest = $this->request->get($field['name']);

            if (isset($field['type']) && $field['type'] === 'checkbox') {
                return [$field['name'] => $fieldInRequest ? Arr::first($fieldInRequest) : false];
            }

            return [$field['name'] => $fieldInRequest];
        });
    }

    /**
     * @return bool
     */
    private function shouldReplaceMedia($id)
    {
        return is_numeric($id) ? $this->repository->whereId($id)->exists() : false;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete()
    {
        if ($this->repository->bulkDelete(explode(',', $this->request->get('ids')))) {
            // $this->fireEvent();

            return $this->respondWithSuccess(___('listing.bulk-delete.success', ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(___('listing.bulk-delete.error', ['modelTitle' => $this->modelTitle]));
    }
}
