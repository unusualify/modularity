<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Unusualify\Modularous\Contracts\ModuleableInterface;
use Unusualify\Modularous\Entities\Enums\AssignmentStatus;
use Unusualify\Modularous\Facades\Filepond;
use Unusualify\Modularous\Facades\HostRoutingRegistrar;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Services\MessageStage;
use Unusualify\Modularous\Traits\ManageModuleRoute;
use Unusualify\Modularous\Traits\ManageNames;
use Unusualify\Modularous\Traits\ManageTraits;
use Unusualify\Modularous\Traits\Moduleable;

abstract class CoreController extends LaravelController implements ModuleableInterface
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests,
        ManageNames,
        Moduleable,
        ManageModuleRoute,
        ManageTraits;

    /**
     * @var Application
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
    protected $modelName;

    /**
     * @var object
     */
    protected $config;

    protected ?Repository $repository;

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;

        $this->baseKey = modularousBaseKey();
        $this->request = $request;

        $this->moduleName = $this->getModuleName();
        $this->module = $this->getModule();
        // $this->config = $this->getModuleConfig();

        $this->namespace = $this->getNamespace();
        $this->routeName = $this->setupRouteName();

        $this->modelName = $this->getModelName();
        $this->repository = $this->getRepository();
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

    public function preload()
    {
        $this->config = $this->getModuleConfig();

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method();
        }
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

    public function getModuleName(): ?string
    {
        return $this->moduleName ?? curtModuleName(dirname((new \ReflectionClass(get_class($this)))->getFileName()));
    }

    /**
     * @return string
     */
    protected function setupRouteName()
    {
        return $this->routeName ?? $this->getRouteName() ?? $this->moduleName;
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
     * @return Repository | null
     */
    public function getRepository()
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
     * @return \Unusualify\Modularity\Transformers\
     */
    protected function getTransformer($data = [])
    {
        if (! ($concrete = $this->getTransformerClass())) {
            return $data;
        }
        if ($data instanceof AbstractPaginator || $data instanceof Collection) {
            return $concrete::collection($data);
        }
        return App::makeWith($concrete, ['resource' => $data]);
    }
    /**
     * @return Transformers
     */
    protected function getTransformerClass()
    {
        if (@class_exists($class = "$this->namespace\Transformers\\" . $this->modelName . 'Resource')) {
            return $class;
        }
        return null;
    }

    /**
     * getModuleConfig
     *
     * @return \StdClass::class
     */
    public function getModuleConfig()
    {
        // $snakeCase = $this->getSnakeCase($this->moduleName);

        return array_to_object($this->module ? $this->module->getRawConfig() : []);

        // return array_to_object(Config::get(modularousBaseKey() . '.system_modules.' . $snakeCase) ?: Config::get($snakeCase)) ?? $this->module->getRawConfig();
    }

    protected function getConfigFieldsByRoute($fieldName, $default = null)
    {
        try {
            return data_get($this->config->routes->{$this->getSnakeCase($this->routeName)}, $fieldName) ?? $default;
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

    protected function getConfigFieldsByRouteRaw($fieldName, $default = null)
    {
        return data_get($this->module ? $this->module->getRawConfig() : [], 'routes.' . $this->getSnakeCase($this->routeName) . '.' . $fieldName) ?? $default;
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
        // $hostRoutingArguments = @class_exists('Unusualify\Modularous\Facades\HostRouting')
        //     ? \Unusualify\Modularous\Facades\HostRouting::getRouteArguments()
        //     : [];
        // return $this->request->route()
        //     ? array_merge($this->request->route()->parameters(), $hostRoutingArguments)
        //     : [];
        $hostRoutingArguments = @class_exists('Unusualify\Modularous\Facades\HostRoutingRegistrar')
                                ? HostRoutingRegistrar::getRouteArguments()
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
        $name = $this->request->input('value');
        $model = $this->repository
            ->getModel();

        // Create new tag with namespace
        $tag = $model->createTagsModel()->create([
            'name' => $name,
            'slug' => Str::slug($name),
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

            $lastAssignment = $assignable->lastAssignment;
            $lastAssignment->update([
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            if ($lastAssignment->wasChanged()) {
                $assignable->touch();
            }

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
                $assignable->touch();
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

        if ($preliminaries = $this->request->get('preliminaries')) {
            Filepond::saveFile($assignment, $preliminaries, 'preliminaries');
        }

        $assignable->touch();

        $assignment->refresh();

        return Response::json($assignment);
    }
}
