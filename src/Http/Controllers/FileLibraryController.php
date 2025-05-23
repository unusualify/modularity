<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Http\Requests\FileRequest;
use Unusualify\Modularity\Services\Uploader\SignAzureUpload;
use Unusualify\Modularity\Services\Uploader\SignS3Upload;
use Unusualify\Modularity\Services\Uploader\SignUploadListener;

class FileLibraryController extends BaseController implements SignUploadListener
{
    /**
     * @var string
     */
    protected $moduleName = 'File';

    /**
     * @var string
     */
    protected $routePrefix = 'file-library';

    /**
     * @var string
     */
    // protected $namespace = 'Unusualify\Modularity';
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
     * @var Illuminate\Routing\UrlGenerator
     */
    protected $urlGenerator;

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
        Request $request,
        UrlGenerator $urlGenerator,
        ResponseFactory $responseFactory,
        Config $config
    ) {
        parent::__construct($app, $request);
        $this->urlGenerator = $urlGenerator;
        $this->responseFactory = $responseFactory;
        $this->config = $config;

        // $this->removeMiddleware('can:edit');
        // $this->middleware('can:edit', ['only' => ['signS3Upload', 'signAzureUpload', 'tags', 'store', 'singleUpdate', 'bulkUpdate']]);
        $this->endpointType = $this->config->get(modularityBaseKey() . '.file_library.endpoint_type');
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
                return $this->buildFile($item);
            })->toArray(),
            'maxPage' => $items->lastPage(),
            'total' => $items->total(),
            'tags' => $this->repository->getTagsList(),
        ];
    }

    /**
     * @param \Unusualify\Modularity\Models\File $item
     * @return array
     */
    private function buildFile($item)
    {
        $routeNamePrefix = adminRouteNamePrefix() ? adminRouteNamePrefix() . '.' : '';

        return $item->mediableFormat() + [
            'tags' => $item->tags->map(function ($tag) {
                return $tag->name;
            }),
            'deleteUrl' => $item->canDeleteSafely() ? moduleRoute($this->moduleName, $routeNamePrefix . $this->routePrefix, 'destroy', ['file' => $item->id]) : null,
            'updateUrl' => $this->urlGenerator->route(Route::hasAdmin('file-library.file.single-update')),
            'updateBulkUrl' => $this->urlGenerator->route(Route::hasAdmin('file-library.file.bulk-update')),
            'deleteBulkUrl' => $this->urlGenerator->route(Route::hasAdmin('file-library.file.bulk-delete')),
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
     * @return JsonResponse
     *
     * @throws BindingResolutionException
     */
    public function store($parentModuleId = null)
    {
        $request = $this->app->make(FileRequest::class);

        if ($this->endpointType === 'local') {
            $file = $this->storeFile($request);
        } else {
            $file = $this->storeReference($request);
        }

        return $this->responseFactory->json(['media' => $this->buildFile($file), 'success' => true], 200);
    }

    /**
     * @param Request $request
     * @return \Unusualify\Modularity\Models\File
     */
    public function storeFile($request)
    {
        $filename = $request->input('qqfilename');

        $cleanFilename = preg_replace("/\s+/i", '-', $filename);

        $fileDirectory = $request->input('unique_folder_name');

        $uuid = $request->input('unique_folder_name') . '/' . $cleanFilename;

        if ($this->config->get(modularityBaseKey() . '.file_library.prefix_uuid_with_local_path', false)) {
            $prefix = trim($this->config->get(modularityBaseKey() . '.file_library.local_path'), '/ ') . '/';
            $fileDirectory = $prefix . $fileDirectory;
            $uuid = $prefix . $uuid;
        }

        $disk = $this->config->get(modularityBaseKey() . '.file_library.disk');

        $request->file('qqfile')->storeAs($fileDirectory, $cleanFilename, $disk);

        $fields = [
            'uuid' => $uuid,
            'filename' => $cleanFilename,
            'size' => $request->input('qqtotalfilesize'),
        ];

        if ($this->shouldReplaceFile($id = $request->input('media_to_replace_id'))) {
            $file = $this->repository->whereId($id)->first();
            $this->repository->afterDelete($file);
            $file->update($fields);

            return $file->fresh();
        } else {
            return $this->repository->create($fields);
        }
    }

    /**
     * @param Request $request
     * @return \Unusualify\Modularity\Models\File
     */
    public function storeReference($request)
    {
        $fields = [
            'uuid' => $request->input('key') ?? $request->input('blob'),
            'filename' => $request->input('name'),
        ];

        if ($this->shouldReplaceFile($id = $request->input('media_to_replace_id'))) {
            $file = $this->repository->whereId($id)->first();
            $this->repository->afterDelete($file);
            $file->update($fields);

            return $file->fresh();
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
            $this->request->only('tags')
        );

        return $this->responseFactory->json([], 200);
    }

    /**
     * @return JsonResponse
     */
    public function bulkUpdate()
    {
        $ids = explode(',', $this->request->input('ids'));

        $previousCommonTags = $this->repository->getTags(null, $ids);
        $newTags = array_filter(explode(',', $this->request->input('tags')));

        foreach ($ids as $id) {
            $this->repository->update($id, ['bulk_tags' => $newTags, 'previous_common_tags' => $previousCommonTags]);
        }

        $scopes = $this->filterScope(['id' => $ids]);
        $items = $this->getIndexItems(scopes: $scopes, forcePagination: false);

        return $this->responseFactory->json([
            'items' => $items->map(function ($item) {
                return $this->buildFile($item);
            })->toArray(),
            'tags' => $this->repository->getTagsList(),
        ], 200);
    }

    /**
     * @return mixed
     */
    public function signS3Upload(Request $request, SignS3Upload $signS3Upload)
    {
        return $signS3Upload->fromPolicy($request->getContent(), $this, $this->config->get(modularityBaseKey() . '.file_library.disk'));
    }

    /**
     * @return mixed
     */
    public function signAzureUpload(Request $request, SignAzureUpload $signAzureUpload)
    {
        return $signAzureUpload->getSasUrl($request, $this, $this->config->get(modularityBaseKey() . '.file_library.disk'));
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
     * @return bool
     */
    private function shouldReplaceFile($id)
    {
        return is_numeric($id) ? $this->repository->whereId($id)->exists() : false;
    }
}
