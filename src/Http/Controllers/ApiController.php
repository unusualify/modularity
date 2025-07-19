<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Unusualify\Modularity\Http\Controllers\Traits\ApiAuthentication;
use Unusualify\Modularity\Http\Controllers\Traits\ApiFiltering;
use Unusualify\Modularity\Http\Controllers\Traits\ApiPagination;
use Unusualify\Modularity\Http\Controllers\Traits\ApiRateLimiting;
use Unusualify\Modularity\Http\Controllers\Traits\ApiRelationships;
use Unusualify\Modularity\Http\Controllers\Traits\ApiResponses;
use Unusualify\Modularity\Http\Controllers\Traits\ApiSorting;
use Unusualify\Modularity\Http\Controllers\Traits\ApiValidation;
use Unusualify\Modularity\Http\Controllers\Traits\ApiVersioning;

abstract class ApiController extends CoreController
{
    use ApiResponses,
        ApiVersioning,
        ApiAuthentication,
        ApiRateLimiting,
        ApiValidation,
        ApiPagination,
        ApiFiltering,
        ApiSorting,
        ApiRelationships;

    /**
     * API version
     *
     * @var string
     */
    protected $apiVersion = 'v1';

    /**
     * Default items per page for API responses
     *
     * @var int
     */
    protected $defaultPerPage = 15;

    /**
     * Maximum items per page allowed
     *
     * @var int
     */
    protected $maxPerPage = 100;

    /**
     * Default includes for API responses
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * Available includes for API responses
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * API resource class
     *
     * @var string
     */
    protected $apiResourceClass;

    /**
     * API resource collection class
     *
     * @var string
     */
    protected $apiResourceCollectionClass;

    /**
     * Whether to wrap responses in data envelope
     *
     * @var bool
     */
    protected $wrapResponses = true;

    /**
     * Custom response metadata
     *
     * @var array
     */
    protected $responseMetadata = [];

    public function __construct(
        Application $app,
        Request $request
    ) {
        parent::__construct($app, $request);

        // Set API-specific configurations
        $this->setApiVersion();
        $this->setApiResourceClasses();
        $this->setApiDefaults();

        $this->__afterConstruct($app, $request);
    }

    /**
     * Set API version from request or default
     */
    protected function setApiVersion(): void
    {
        $version = $this->request->header('API-Version') ??
                   $this->request->get('version') ??
                   $this->apiVersion;

        $this->apiVersion = $version;
    }

    /**
     * Set API resource classes
     */
    protected function setApiResourceClasses(): void
    {
        if (! $this->apiResourceClass) {
            $this->apiResourceClass = $this->getApiResourceClass();
        }

        if (! $this->apiResourceCollectionClass) {
            $this->apiResourceCollectionClass = $this->getApiResourceCollectionClass();
        }
    }

    /**
     * Set API defaults
     */
    protected function setApiDefaults(): void
    {
        $this->defaultPerPage = $this->getConfigFieldsByRoute('api.per_page', $this->defaultPerPage);
        $this->maxPerPage = $this->getConfigFieldsByRoute('api.max_per_page', $this->maxPerPage);
        $this->defaultIncludes = $this->getConfigFieldsByRoute('api.default_includes', $this->defaultIncludes);
        $this->availableIncludes = $this->getConfigFieldsByRoute('api.available_includes', $this->availableIncludes);
    }

    /**
     * Get per page value from request
     */
    protected function getPerPage(): int
    {
        $perPage = $this->request->get('per_page', $this->defaultPerPage);

        return min((int) $perPage, $this->maxPerPage);
    }

    /**
     * Get includes from request with validation and constraint support
     */
    protected function getIncludes(): array
    {
        $includes = $this->request->get('include', []);

        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        // Use ApiRelationships trait for validation
        return $this->validateRelationships($includes);
    }

    /**
     * Get includes for eager loading with constraints
     */
    protected function getIncludesForEagerLoading(): array
    {
        $includes = $this->request->get('include', []);

        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        // Parse nested relationships and return validated includes
        $validatedIncludes = $this->validateRelationships($includes);

        return array_merge($this->defaultIncludes, $validatedIncludes);
    }

    /**
     * Get API resource class
     */
    protected function getApiResourceClass(): ?string
    {
        $class = "$this->namespace\\Http\\Resources\\API\\{$this->modelName}Resource";

        if (class_exists($class)) {
            return $class;
        }

        $class = "$this->namespace\\Http\\Resources\\{$this->modelName}Resource";

        if (class_exists($class)) {
            return $class;
        }

        return null;
    }

    /**
     * Get API resource collection class
     */
    protected function getApiResourceCollectionClass(): ?string
    {
        $class = "$this->namespace\\Http\\Resources\\{$this->modelName}Collection";

        if (class_exists($class)) {
            return $class;
        }

        return null;
    }

    /**
     * Get items for API with relationship loading
     *
     * @return mixed
     */
    protected function getApiIndexItems(
        array $with = [],
        array $scopes = [],
        array $orders = [],
        ?int $perPage = null,
        bool $forcePagination = false
    ) {
        $perPage = $perPage ?? $this->getPerPage();
        $includes = $this->getIncludesForEagerLoading();

        // Apply eager loading with constraints using ApiRelationships trait
        $query = $this->repository->query();
        $query = $this->eagerLoadWithConstraints($query, $includes);

        return $this->repository->get(
            with: array_merge($includes, $with),
            scopes: $scopes,
            orders: $orders,
            perPage: $perPage,
            forcePagination: $forcePagination
        );
    }

    /**
     * Get single item with relationships
     *
     * @return mixed
     */
    protected function getApiShowItem(int $id, array $with = [])
    {
        $includes = $this->getIncludesForEagerLoading();

        $item = $this->repository->getById($id, with: array_merge($includes, $with));

        if ($item) {
            // Load additional relationships if specified
            $additionalIncludes = $this->getIncludes();
            if (! empty($additionalIncludes)) {
                $item = $this->loadRelationships($item, $additionalIncludes);
            }
        }

        return $item;
    }

    /**
     * Respond with a resource
     *
     * @param mixed $resource
     */
    protected function respondWithResource($resource, int $status = 200): JsonResponse
    {
        if ($this->apiResourceClass) {
            $resource = new $this->apiResourceClass($resource);
        }

        return $this->respondWithData($resource, $status);
    }

    /**
     * Respond with a collection
     *
     * @param mixed $collection
     */
    protected function respondWithCollection($collection, int $status = 200): JsonResponse
    {
        if ($this->apiResourceCollectionClass) {
            $collection = new $this->apiResourceCollectionClass($collection);
        } elseif ($this->apiResourceClass) {
            $collection = $this->apiResourceClass::collection($collection);
        }

        return $this->respondWithData($collection, $status);
    }

    /**
     * Respond with data
     *
     * @param mixed $data
     */
    protected function respondWithData($data, int $status = 200): JsonResponse
    {
        $response = $this->wrapResponses ? ['data' => $data] : $data;

        if (! empty($this->responseMetadata)) {
            $response['meta'] = $this->responseMetadata;
        }

        $jsonResponse = Response::json($response, $status);

        // Add rate limit headers to response
        foreach ($this->getRateLimitHeaders() as $header => $value) {
            $jsonResponse->header($header, $value);
        }

        return $jsonResponse;
    }

    /**
     * Validate API request
     */
    protected function validateApiRequest(): void
    {
        // Apply rate limiting first
        $rateLimitResponse = $this->applyRateLimit();
        if ($rateLimitResponse) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException($rateLimitResponse);
        }

        // Validate pagination parameters (ApiValidation trait)
        $this->validatePagination();

        // Validate includes parameter (ApiValidation trait)
        $this->validateIncludes();

        // Validate filters (ApiFiltering trait)
        $this->validateFilters();

        // Validate sorting (ApiSorting trait)
        $this->validateSorting();

        // Override in child classes for specific validation
    }

    /**
     * Validate API store request
     */
    protected function validateApiStore(): array
    {
        return $this->validateFormRequest()->all();
    }

    /**
     * Validate API update request
     *
     * @param mixed $item
     */
    protected function validateApiUpdate($item): array
    {
        return $this->validateFormRequest()->all();
    }

    /**
     * Get bulk users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulk()
    {
        // Apply rate limiting
        $rateLimitResponse = $this->applyRateLimit();
        if ($rateLimitResponse) {
            return $rateLimitResponse;
        }

        $ids = $this->request->get('ids', []);
        $includes = $this->getIncludesForEagerLoading();

        $users = $this->repository->getByIds($ids, with: $includes);

        return $this->respondWithCollection($users);
    }

    /**
     * Search users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        // Apply rate limiting
        $rateLimitResponse = $this->applyRateLimit();
        if ($rateLimitResponse) {
            return $rateLimitResponse;
        }

        $query = $this->request->get('q', '');
        $includes = $this->getIncludesForEagerLoading();

        $users = $this->repository->search($query, with: $includes);

        return $this->respondWithCollection($users);
    }

    /**
     * Get available filters
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filters()
    {
        // Apply rate limiting
        $rateLimitResponse = $this->applyRateLimit();
        if ($rateLimitResponse) {
            return $rateLimitResponse;
        }

        return $this->respondWithData([
            'filters' => $this->availableFilters,
            'sorts' => $this->availableSorts,
            'includes' => $this->availableIncludes,
        ]);
    }

    /**
     * Get resource metadata with relationship counts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function meta()
    {
        // Apply rate limiting
        $rateLimitResponse = $this->applyRateLimit();
        if ($rateLimitResponse) {
            return $rateLimitResponse;
        }

        $metadata = [
            'total_count' => $this->repository->getCountForAll(),
            'active_count' => $this->repository->getCountForPublished(),
            // 'recent_count' => $this->repository->getCountForDraft(),
            'available_includes' => $this->availableIncludes,
            'available_scopes' => $this->availableScopes,
            'available_filters' => $this->availableFilters,
            'available_sorts' => $this->availableSorts,
            'available_search' => $this->availableSearch,
        ];

        // Add relationship counts if available
        // if (!empty($this->availableIncludes)) {
        //     $sampleModel = $this->repository->newQuery()->first();
        //     if ($sampleModel) {
        //         $relationshipCounts = [];
        //         foreach ($this->availableIncludes as $relationship) {
        //             if ($this->isRelationshipAllowed($relationship)) {
        //                 $relationshipCounts[$relationship] = $this->getRelationshipCount($sampleModel, $relationship);
        //             }
        //         }
        //         $metadata['relationship_counts'] = $relationshipCounts;
        //     }
        // }

        return $this->respondWithData($metadata);
    }

    /**
     * Get paginated index data for API
     */
    public function index(): JsonResponse
    {
        $this->validateApiRequest();

        $perPage = $this->getPerPage();
        $filters = $this->getFilters();
        $sorts = $this->getSorts();
        $includes = $this->getIncludesForEagerLoading();
        $scopes = $this->getScopes();

        $items = $this->getApiIndexItems(
            with: $includes,
            scopes: array_merge($scopes, $filters),
            orders: $sorts,
            perPage: $perPage,
            forcePagination: true
        );

        return $this->respondWithCollection($items);
    }

    /**
     * Show a single resource
     */
    public function show(int $id): JsonResponse
    {
        $this->validateApiRequest();

        $item = $this->getApiShowItem($id);

        if (! $item) {
            return $this->respondNotFound();
        }

        return $this->respondWithResource($item);
    }

    /**
     * Store a new resource
     */
    public function store(): JsonResponse
    {
        $this->validateApiRequest();

        $validatedData = $this->validateApiStore();

        $item = $this->repository->create($validatedData);

        // Load relationships on created item
        $includes = $this->getIncludesForEagerLoading();
        if (! empty($includes)) {
            $item = $this->loadRelationships($item, $includes);
        }

        return $this->respondWithResource($item, 201);
    }

    /**
     * Update an existing resource
     */
    public function update(int $id): JsonResponse
    {
        $this->validateApiRequest();

        $item = $this->repository->getById($id);

        if (! $item) {
            return $this->respondNotFound();
        }

        $validatedData = $this->validateApiUpdate($item);

        $updatedItem = $this->repository->update($id, $validatedData);

        // Load relationships on updated item
        $includes = $this->getIncludesForEagerLoading();
        if (! empty($includes)) {
            $updatedItem = $this->loadRelationships($updatedItem, $includes);
        }

        return $this->respondWithResource($updatedItem);
    }

    /**
     * Delete a resource
     */
    public function destroy(int $id): JsonResponse
    {
        $this->validateApiRequest();

        $item = $this->repository->getById($id);

        if (! $item) {
            return $this->respondNotFound();
        }

        $this->repository->delete($id);

        return $this->respondWithMessage('Resource deleted successfully');
    }
}
