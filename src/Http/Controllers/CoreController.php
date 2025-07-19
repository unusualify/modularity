<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
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
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

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
     * @var object
     */
    protected $config;

    /**
     * @var \Unusualify\Modularity\Repositories\ModuleRepository
     */
    protected $repository;

    /**
     * @var \Unusualify\Modularity\Module
     */
    protected $module;

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;

        $this->baseKey = modularityBaseKey();

        $this->request = $request;

        $this->moduleName = $this->getModuleName();
        $this->module = Modularity::find($this->moduleName);
        $this->config = $this->getModuleConfig();

        $this->namespace = $this->getNamespace();
        $this->routeName = $this->getRouteName();


        $this->modelName = $this->getModelName();
        $this->repository = $this->getRepository();
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
     * getModuleConfig
     *
     * @return \StdClass::class
     */
    public function getModuleConfig()
    {
        $snakeCase = $this->getSnakeCase($this->moduleName);

        return array_to_object(Config::get(modularityBaseKey() . '.system_modules.' . $snakeCase) ?: Config::get($snakeCase));
    }

    protected function getConfigFieldsByRoute($field_name, $default = null)
    {
        try {
            return data_get($this->config->routes->{$this->getSnakeCase($this->routeName)}, $field_name) ?? $default;

            return $this->config->routes->{$this->getSnakeCase($this->routeName)}->{$field_name};
        } catch (\Throwable $th) {
            return $default;
            dd(
                // $th,
                $this,
                debug_backtrace()
            );
        }

        return $this->config->routes->{$this->getSnakeCase($this->routeName)}->{$field_name};
        // return $this->isParentRoute()
        //     ? $this->config->parent_route->{$field_name}
        //     : $this->config->sub_routes->{$this->getSnakeCase($this->routeName)}->{$field_name};
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
