<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Http\Controllers\Traits\ManageResourceCache;
use Unusualify\Modularous\Module;

class ControllerUsingManageResourceCache extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ManageResourceCache;

    public $repository;

    public $user;

    public array $tableActions = [];

    protected ?string $moduleName = null;

    protected ?string $routeName = null;

    protected ?Module $module = null;

    public function setModuleName(?string $moduleName): void
    {
        $this->moduleName = $moduleName;
    }

    public function setRouteName(?string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function setModule(?Module $module): void
    {
        $this->module = $module;
    }

    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function invokeSetTableActionsManageResourceCache(): void
    {
        $this->setTableActionsManageResourceCache();
    }

    /**
     * @param array<string, mixed> $def
     * @return array<string, mixed>|null
     */
    public function invokeMapResourceCacheTableAction(array $def, string $routePrefix): ?array
    {
        return $this->mapResourceCacheTableAction($def, $routePrefix);
    }

    public function invokeAuthorizeResourceCacheAction(): void
    {
        $this->authorizeResourceCacheAction();
    }

    /**
     * @return array<int, string>
     */
    public function invokeAddFormAppendsManageResourceCache(): array
    {
        return $this->addFormAppendsManageResourceCache();
    }

    /**
     * @return array<int, string>
     */
    public function invokeAddIndexAppendsManageResourceCache(): array
    {
        return $this->addIndexAppendsManageResourceCache();
    }

    /**
     * @return array<string, bool>
     */
    public function invokeResolveResourceCacheTypes(Request $request): array
    {
        return $this->resolveResourceCacheTypes($request);
    }
}
