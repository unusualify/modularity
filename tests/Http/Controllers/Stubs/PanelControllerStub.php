<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Stubs;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Unusualify\Modularous\Http\Controllers\PanelController;
use Unusualify\Modularous\Repositories\Repository;

class PanelControllerStub extends PanelController
{
    protected $moduleName = 'TestModule';

    protected $routeName = 'Item';

    protected $setDefaultPermissions = false;

    protected $formSchema = [];

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

    public function exposeGetRoutePrefix(): string
    {
        return $this->getRoutePrefix();
    }

    public function exposeGenerateRoutePrefix(bool $noNested = false): string
    {
        return $this->generateRoutePrefix($noNested);
    }

    public function exposeNestedParentScopes(): array
    {
        return $this->nestedParentScopes();
    }

    public function exposeGetIndexOption(string $option): bool
    {
        return $this->getIndexOption($option);
    }

    public function exposeGetJSONData(array $with = [])
    {
        return $this->getJSONData($with);
    }

    public function exposeGetFormRequestClass($schema = null)
    {
        return $this->getFormRequestClass($schema);
    }

    public function exposeRemoveMiddleware(string $middleware): void
    {
        $this->removeMiddleware($middleware);
    }

    public function exposeIsRelationField(string $key): bool
    {
        return $this->isRelationField($key);
    }

    public function exposeGetReplaceUrl(): bool
    {
        return $this->getReplaceUrl();
    }

    public function exposeGetFormattedIndexItems(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $this->getFormattedIndexItems($paginator);
    }

    public function exposeValidateFormRequest($schema = [])
    {
        return $this->validateFormRequest($schema);
    }

    public function setIsParent(bool $isParent): void
    {
        $this->isParent = $isParent;
    }

    public function exposeIsParent(): bool
    {
        return (bool) $this->isParent;
    }

    public function exposeModelTitle(): string
    {
        return $this->modelTitle;
    }

    public function exposeIsGateable(): bool
    {
        return $this->isGateable();
    }

    public function configureNested(int $parentId, string $parentName, mixed $parentModel = null): void
    {
        $this->isNested = true;
        $this->nestedParentId = $parentId;
        $this->nestedParentName = $parentName;
        $this->nestedParentModel = $parentModel;
    }

    public function exposeGetParentModuleForeignKey(): string
    {
        return $this->getParentModuleForeignKey();
    }

    public function exposeGetModuleRoute(int $id, string $action, bool $singleton = false): string
    {
        return $this->getModuleRoute($id, $action, $singleton);
    }

    public function exposeTitleIsTranslatable(): bool
    {
        return $this->titleIsTranslatable();
    }

    public function exposeIsParentRoute(): bool
    {
        return $this->isParentRoute();
    }
}
