<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Http\Controllers\Traits\Table\TableRows;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Traits\ManageNames;

class ControllerUsingTableRows extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ManageNames, TableRows;

    public $repository;

    public $user;

    public ?Module $module = null;

    /** @var array<string, bool> */
    public array $indexOptions = [];

    /** @var array<string, mixed> */
    public array $tableAttributes = [];

    /** @var array<string, mixed> */
    public array $configFieldsByRoute = [];

    public array $tableActions = [];

    protected ?string $moduleName = 'TestModule';

    protected ?string $routeName = 'TestRoute';

    protected function getIndexOption($option): bool
    {
        return $this->indexOptions[$option] ?? false;
    }

    protected function getConfigFieldsByRoute($fieldName, $default = null)
    {
        return $this->configFieldsByRoute[$fieldName] ?? $default;
    }

    /**
     * @param array<int, array<string, mixed>> $schema
     * @return array<int, array<string, mixed>>
     */
    public function createFormSchema(array $schema): array
    {
        return $schema;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function invokeGetTableRowActions(): array
    {
        return $this->getTableRowActions();
    }
}
