<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Http\Controllers\Traits\ManageScopes;
use Unusualify\Modularous\Http\Controllers\Traits\Table\TableFilters;
use Unusualify\Modularous\Traits\ManageNames;

class ControllerUsingTableFilters extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ManageNames, ManageScopes, TableFilters;

    public $repository;

    public Request $request;

    public $config = true;

    public $user;

    /** @var array<string, mixed> */
    public array $configFieldsByRoute = [];

    /** @var array<string, mixed> */
    public array $configFieldsByRouteRaw = [];

    protected ?string $moduleName = 'TestModule';

    protected ?string $routeName = 'TestRoute';

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function setConfigTableFilters(array $filters): void
    {
        $this->configFieldsByRoute['table_filters'] = $filters;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function setRawFiltersConfig(array $filters): void
    {
        $this->configFieldsByRouteRaw['filters'] = $filters;
    }

    protected function getConfigFieldsByRoute($fieldName, $default = null)
    {
        return $this->configFieldsByRoute[$fieldName] ?? $default;
    }

    protected function getConfigFieldsByRouteRaw($fieldName, $default = null)
    {
        return $this->configFieldsByRouteRaw[$fieldName] ?? $default;
    }

    protected function getIndexOption($option): bool
    {
        return match ($option) {
            'restore' => true,
            'publish' => false,
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function nestedParentScopes(): array
    {
        return ['parent_id' => 1];
    }

    /**
     * @param array<string, mixed> $scopes
     * @return array<int, array<string, mixed>>
     */
    public function invokeGetCountsList(array $scopes = []): array
    {
        return $this->getCountsList($scopes);
    }

    /**
     * @param array<string, mixed> $filter
     */
    public function invokeHandleFilterCount(array $filter): int
    {
        return $this->handleFilterCount($filter);
    }

    /**
     * @param array<string, mixed> $scopes
     * @return array<int, array<string, mixed>>
     */
    public function invokeGetTableMainFilters(array $scopes = []): array
    {
        return $this->getTableMainFilters($scopes);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function invokeGetMainCountsList(): array
    {
        return $this->getMainCountsList();
    }

    /**
     * @return array<string, mixed>
     */
    public function invokeGetTableAdvancedFilters(): array
    {
        return $this->getTableAdvancedFilters();
    }

    /**
     * @param array<string, mixed> $filter
     * @return array<string, mixed>
     */
    public function invokeColumnsFilterConfiguration(array $filter): array
    {
        return $this->columnsFilterConfiguration($filter);
    }

    /**
     * @param array<string, mixed> $filter
     * @return array<string, mixed>
     */
    public function invokeCalibrateFilter(array $filter): array
    {
        return $this->calibrateFilter($filter);
    }

    /**
     * @param array<string, mixed> $filter
     * @return array<string, mixed>
     */
    public function invokeGetTableAdvancedFiltersDatePicker(array $filter): array
    {
        return $this->getTableAdvancedFiltersDatePicker($filter);
    }
}
