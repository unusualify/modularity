<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Http\Controllers\Traits\ManageScopes;
use Unusualify\Modularous\Traits\ManageNames;

class ControllerUsingManageScopes extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ManageNames, ManageScopes;

    public Request $request;

    public $config = null;

    public string $titleColumnKey = 'name';

    /** @var array<string, array<string, mixed>> */
    public array $indexColumns = [
        'name' => ['sortBy' => 'name'],
    ];

    /** @var array<string, mixed> */
    public array $configFieldsByRoute = [];

    protected ?string $moduleName = 'TestModule';

    protected ?string $routeName = 'TestRoute';

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function setFixedFilters(array $filters): void
    {
        $this->fixedFilters = $filters;
    }

    /**
     * @param array<string, mixed> $scopes
     */
    public function setConfigScopes(array $scopes): void
    {
        $this->configFieldsByRoute['scopes'] = $scopes;
    }

    /**
     * @param array<int, object> $filters
     */
    public function setConfigTableFilters(array $filters): void
    {
        $this->configFieldsByRoute['table_filters'] = $filters;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setFiltersDefaultOptions(array $options): void
    {
        $this->filtersDefaultOptions = $options;
    }

    public function setTableOrders(array $orders): void
    {
        $this->tableOrders = $orders;
    }

    protected function getConfigFieldsByRoute($fieldName, $default = null)
    {
        return $this->configFieldsByRoute[$fieldName] ?? $default;
    }

    /**
     * @param array<string, mixed> $prepend
     * @return array<string, mixed>
     */
    public function invokeFilterScope(array $prepend = []): array
    {
        return $this->filterScope($prepend);
    }

    /**
     * @return array<string, mixed>
     */
    public function invokeGetRequestFilters(): array
    {
        return $this->getRequestFilters();
    }

    public function invokeApplyFiltersDefaultOptions(): void
    {
        $this->applyFiltersDefaultOptions();
    }

    /**
     * @return array<string, string>
     */
    public function invokeOrderScope(): array
    {
        return $this->orderScope();
    }

    /**
     * @return array<string, mixed>
     */
    public function invokeGetExactScope(): array
    {
        return $this->getExactScope();
    }

    /**
     * @return array<string, mixed>
     */
    public function invokeGetTableOrders(): array
    {
        return $this->getTableOrders();
    }

    public function invokePreloadManageScopes(): void
    {
        $this->preloadManageScopes();
    }

    public function getDefaultTableOrders(): array
    {
        return $this->defaultTableOrders;
    }

    public function invokeAfterConstructManageScopes($app, Request $request): void
    {
        $this->__afterConstructManageScopes($app, $request);
    }
}
