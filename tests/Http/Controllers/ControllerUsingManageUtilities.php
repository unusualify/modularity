<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Traits\ManageNames;

class ControllerUsingManageUtilities extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ManageNames, ManageUtilities;

    public Request $request;

    public ?Module $module = null;

    public $repository;

    protected ?string $moduleName = 'TestModule';

    protected ?string $routeName = 'TestRoute';

    protected ?string $routePrefix = null;

    protected $isNested = false;

    protected $titleColumnKey = 'title';

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setTableAttributes(array $attributes): void
    {
        $this->tableAttributes = $attributes;
    }

    /**
     * @param array<int, array<string, mixed>> $schema
     */
    public function setFormSchema(array $schema): void
    {
        $this->formSchema = $schema;
    }

    /**
     * @param array<string, mixed> $prepend
     * @return array<string, mixed>
     */
    protected function filterScope($prepend = [])
    {
        return $prepend;
    }

    protected function filterHeadersByRoles(array $headers): array
    {
        return $headers;
    }

    protected function getIndexTableColumns(): array
    {
        return [['key' => 'title', 'title' => 'Title']];
    }

    protected function nestedParentScopes(): array
    {
        return [];
    }

    protected function hydrateTableAttributes(): array
    {
        return [];
    }

    protected function getIndexUrls(): array
    {
        return ['index' => '/admin/test'];
    }

    protected function getUrls(): array
    {
        return ['store' => '/admin/test/store'];
    }

    protected function getTableMainFilters(array $scopes = []): array
    {
        return [['slug' => 'all', 'name' => 'All']];
    }

    protected function filterSchemaByRoles(array $schema): array
    {
        return $schema;
    }

    protected function routeHas(string $trait): bool
    {
        return false;
    }

    protected function routeHasTrait(string $trait): bool
    {
        return false;
    }

    protected function hasTranslatedInput(array $schema): bool
    {
        return false;
    }

    protected function getTableRowActions(): array
    {
        return [];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function getFormActions(string $scope = 'create'): array
    {
        return [];
    }

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getConfigFieldsByRoute($fieldName, $default = null)
    {
        return $default;
    }

    protected function getVuetifyDatatableOptions(): array
    {
        return ['page' => 1];
    }

    protected function getTableAdvancedFilters(): array
    {
        return [];
    }

    protected function getDefaultTableOptions(): array
    {
        return ['itemsPerPage' => 10];
    }

    protected function getTableDraggableOptions(): array
    {
        return [];
    }

    protected function getRepositoryItem($id = null)
    {
        return null;
    }

    protected function getFormItem($id = null)
    {
        return [];
    }

    protected function getItemIdentifier($item): ?int
    {
        return $item?->id;
    }

    protected function getFormUrl(?int $itemId): string
    {
        return '/admin/test/form';
    }

    protected function localizedPublicPermalinksForFormItem($item): array
    {
        return [];
    }

    protected function signedPublicPreviewFormPayload(?int $itemId): array
    {
        return [];
    }

    public function invokeGetIndexData(array $prependScope = []): array
    {
        return $this->getIndexData($prependScope);
    }

    public function invokeGetFormData(?int $id = null): array
    {
        return $this->getFormData($id);
    }

    public function invokeGetViewLayoutVariables(): array
    {
        return $this->getViewLayoutVariables();
    }

    /**
     * @return array<int, string>
     */
    public function invokeAddIndexWithsNestedData(): array
    {
        return $this->addIndexWithsNestedData();
    }

    public function setCurrentRouteAction(string $action): void
    {
        $route = (new RoutingRoute('GET', '/test', ['uses' => 'Controller@' . $action]));
        Route::shouldReceive('current')->andReturn($route);
    }
}
