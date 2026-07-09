<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Stubs;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\CoreController;
use Unusualify\Modularous\Repositories\Repository;

class CoreControllerStub extends CoreController
{
    protected $moduleName = 'TestModule';

    protected $routeName = 'Item';

    public ?Repository $repositoryOverride = null;

    public function __construct(Application $app, Request $request, ?Repository $repositoryOverride = null)
    {
        $this->repositoryOverride = $repositoryOverride;

        parent::__construct($app, $request);
    }

    protected function getNamespace(): string
    {
        return 'TestModules\\TestModule';
    }

    public function getRepository()
    {
        return $this->repositoryOverride ?? parent::getRepository();
    }

    public function exposeGetTransformer(mixed $data = [])
    {
        return $this->getTransformer($data);
    }

    public function exposeGetTransformerClass(): ?string
    {
        return $this->getTransformerClass();
    }

    public function exposeGetConfigFieldsByRoute(string $fieldName, mixed $default = null): mixed
    {
        return $this->getConfigFieldsByRoute($fieldName, $default);
    }

    public function exposeGetConfigFieldsByRouteRaw(string $fieldName, mixed $default = null): mixed
    {
        return $this->getConfigFieldsByRouteRaw($fieldName, $default);
    }

    public function exposeRouteArguments(): array
    {
        return $this->routeArguments();
    }

    public function exposeRouteModuleArguments(): array
    {
        return $this->routeModuleArguments();
    }

    public function exposeRouteArgument(): mixed
    {
        return $this->routeArgument();
    }

    public function exposeParentRouteArguments(): array
    {
        return $this->parentRouteArguments();
    }

    public function exposeRouteHas(string $behavior): bool
    {
        return $this->routeHas($behavior);
    }

    public function exposePreload(): void
    {
        $this->preload();
    }
}
