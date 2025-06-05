<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Enums\AssignmentStatus;
use Unusualify\Modularity\Facades\Filepond;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Traits\ManageNames;
use Unusualify\Modularity\Traits\ManageTraits;

abstract class CoreController extends LaravelController
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests,
        ManageNames,
        ManageTraits;

    /**
     * baseKey
     *
     * @var string snake_case
     */
    protected $baseKey;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var \Unusualify\Modularity\Repositories\ModuleRepository
     */
    protected $repository;

    /**
     * @var \Unusualify\Modularity\Module
     */
    protected $module;

    public function __construct(Request $request)
    {
        $this->baseKey = modularityBaseKey();

        $this->request = $request;

        $this->moduleName = $this->getModuleName();
        $this->module = Modularity::find($this->moduleName);

        $this->namespace = $this->getNamespace();
        $this->routeName = $this->getRouteName();

        $this->modelName = $this->getModelName();
        $this->repository = $this->getRepository();

    }

    /**
     * @param array $scopes
     * @param bool $forcePagination
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getIndexItems($with = [], $scopes = [], $appends = [], $forcePagination = false)
    {
        $perPage = $this->request->get('itemsPerPage') ?? $this->getTableAttribute('itemsPerPage') ?? $this->perPage ?? 10;

        if (! $this->request->ajax()) {
            $perPage = 0;
        }

        return $this->transformIndexItems($this->repository->get(
            with: ($this->indexWith ?? []) + $with,
            scopes: $scopes,
            orders: $this->orderScope(),
            perPage: $perPage,
            forcePagination: $forcePagination,
            appends: $appends,
            id: $this->request->get('id') ?? null
        ));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function transformIndexItems($items)
    {
        return $items;
    }

    /**
     * @param array $paginator
     * @return array
     */
    public function getFormattedIndexItems($paginator) // getIndexTableItems
    {
        return $paginator;
    }

    /**
     * @return string
     */
    protected function getNamespace()
    {
        try {
            return $this->namespace ?? config('modules.namespace', 'Modules') . "\\{$this->moduleName}";
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /**
     * @return string
     */
    protected function getModuleName()
    {
        return $this->moduleName ?? curtModuleName(dirname((new \ReflectionClass(get_class($this)))->getFileName()));
    }

    /**
     * @return string
     */
    protected function getRouteName()
    {
        return $this->routeName ?? $this->routeName() ?? $this->moduleName;
    }

    /**
     * @return string
     */
    protected function getModelName()
    {
        try {
            return $this->modelName ?? ucfirst($this->routeName);
        } catch (\Throwable $th) {
            dd(
                $this
            );

            return $th;
        }
    }

    /**
     * @return \Unusualify\Modularity\Repositories\ModuleRepository
     */
    protected function getRepository()
    {
        return $this->getRepositoryClass($this->modelName) ? App::make($this->getRepositoryClass($this->modelName)) : null;
        try {
            // code...
        } catch (\Throwable $th) {
            dd(
                "repositoryClass not exists for {$this->routeName} in {$this->moduleName}",
            );
            throw $th;
        }
    }

    /**
     * getRepositoryClass
     *
     * @param mixed $model
     * @return void
     */
    public function getRepositoryClass($model)
    {
        if (@class_exists($class = "$this->namespace\Repositories\\" . $model . 'Repository')) {
            return $class;
        }

        return null;

        // TODO if repository is not exists
        return TwillCapsules::getCapsuleForModel($model)->getRepositoryClass();
    }

    /**
     * @return string
     */
    protected function getModelTitle()
    {
        return $this->getHeadline($this->modelName);
    }

    protected function routeParameters()
    {
        return $this->request->route()
            ? $this->request->route()->parameters()
            : [];
    }

    protected function routeArguments()
    {
        // $hostRoutingArguments = @class_exists('Unusualify\Modularity\Facades\HostRouting')
        //     ? \Unusualify\Modularity\Facades\HostRouting::getRouteArguments()
        //     : [];
        // return $this->request->route()
        //     ? array_merge($this->request->route()->parameters(), $hostRoutingArguments)
        //     : [];
        $hostRoutingArguments = @class_exists('Unusualify\Modularity\Facades\HostRoutingRegistrar')
                                ? \Unusualify\Modularity\Facades\HostRoutingRegistrar::getRouteArguments()
                                : [];

        return $this->request->route()
            ? array_merge($this->request->route()->parameters(), $hostRoutingArguments)
            : [];
    }

    protected function routeModuleArguments()
    {
        return Arr::mapWithKeys($this->routeArguments(), function ($value, $snakeName) {
            return [$this->getStudlyName($snakeName) => $value];
        });
    }

    protected function routeArgument()
    {
        $filtered = Arr::where($this->routeArguments(), function ($value, $snakeName) {
            return $this->getStudlyName($snakeName) == $this->routeName;
        });

        return $filtered[$this->getSnakeCase($this->routeName)] ?? null;
    }

    protected function parentRouteArguments()
    {
        $filtered = Arr::where($this->routeArguments(), function ($value, $snakeName) {
            return $this->getStudlyName($snakeName) !== $this->routeName;
        });

        return Arr::mapWithKeys($filtered, function ($value, $snakeName) {
            return [$this->getStudlyName($snakeName) => $value];
        });
    }

    /**
     * @param string $behavior
     * @return bool
     */
    protected function routeHasTrait($behavior)
    {
        return $this->repository->hasBehavior($behavior);
    }

    /**
     * @param string $behavior
     * @return bool
     */
    protected function routeHas($behavior)
    {
        return $this->repository->hasBehavior($behavior);
    }

    /**
     * @return void
     */
    public function __afterConstruct(...$args)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method(...$args);
        }
    }

    /**
     * @return void
     */
    public function __beforeConstruct(...$args)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method(...$args);
        }
    }

    /**
     * tags
     *
     * @return Illuminate\Support\Facades\Response
     */
    public function tags()
    {
        $query = $this->request->input('q');

        if (is_null($query)) {
            $query = '';
        }
        // dd($query, $this->repository);

        $tags = $this->repository->getTags($query);

        return Response::json(
            [
                'resource' => [
                    'last_page' => 1,
                    'data' => $tags->map(function ($tag) {
                        return $tag->name;
                    }),
                ],
            ], 200);
    }

    /**
     * update Tags
     *
     * @return Illuminate\Support\Facades\Response
     */
    public function tagsUpdate()
    {

        // dd($this->request->all());
        // $this->repository
        //     ->getModel()
        //     ->addTag($this->request->input('value'));

        $name = $this->request->input('value');
        $model = $this->repository
            ->getModel();

        // $model->addTag($name);
        // // $model->removeTag($this->request->input('value'));

        // $tag = $model->createTagsModel()
        //     ->select(['id'])
        //     ->whereName($name)
        //     ->whereNamespace(get_class($model))
        //     ->first();

        // Create new tag with namespace
        $tag = $model->createTagsModel()->create([
            'name' => $this->request->input('value'),
            'slug' => Str::slug($this->request->input('value')),
            'namespace' => get_class($model),
        ]);

        return Response::json([
            'message' => 'Tag created successfully',
            'variant' => MessageStage::SUCCESS,
            'id' => $tag->id,
        ], 200);

        return Response::json(
            [
                'resource' => [
                    'last_page' => 1,
                    'data' => $tags->map(function ($tag) {
                        return $tag->name;
                    }
                    ),
                ],
            ], 200);

    }

    public function assignments($id)
    {
        $assignments = $this->repository->getAssignments($id);

        return Response::json($assignments);
    }

    public function createAssignment($id)
    {
        if (($status = $this->request->get('status'))) {
            $assignable = $this->repository->getById($id);

            $assignable->lastAssignment->update([
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            return Response::json([
                'location' => 'top',
                'variant' => MessageStage::SUCCESS,
                'message' => __('Assignment updated successfully!'),
                'assignments' => $this->repository->getAssignments($id),
            ]);
        }

        if (($attachments = $this->request->get('attachments'))) {
            $assignable = $this->repository->getById($id);

            $lastAssignment = $assignable->lastAssignment;

            if ($attachments) {
                Filepond::saveFile($lastAssignment, $attachments, 'attachments');
            }

            return Response::json([
                'location' => 'top',
                'variant' => MessageStage::SUCCESS,
                'message' => __('Attachments saved successfully!'),
                'assignments' => $this->repository->getAssignments($id),
            ]);
        }

        $this->validate($this->request, [
            'assignee_id' => 'required|',
            'assignee_type' => 'required',

            'assignable_id' => 'required',
            'assignable_type' => 'required',

            // 'title' => 'required',
            'description' => 'required',
            'due_at' => 'required|date',
        ]);

        $assignable = $this->repository->getById($id);

        if ($assignable->lastAssignment && $assignable->lastAssignment->status !== AssignmentStatus::COMPLETED) {
            $assignable->lastAssignment->updateQuietly([
                'status' => AssignmentStatus::CANCELLED,
            ]);
        }

        $assignment = $assignable->assignments()->create($this->request->only([
            'assignee_id',
            'assignee_type',
            'due_at',
            'description',
        ]));

        $assignment->refresh();

        return Response::json($assignment);
    }
}
