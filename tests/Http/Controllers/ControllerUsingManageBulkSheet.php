<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Contracts\CanBulkSheet;
use Unusualify\Modularous\Http\Controllers\Traits\ManageBulkSheet;
use Unusualify\Modularous\Module;

class ControllerUsingManageBulkSheet extends Controller implements CanBulkSheet
{
    use AuthorizesRequests, ValidatesRequests, ManageBulkSheet;

    public Application $app;

    public ?Module $module = null;

    public array $tableActions = [];

    protected ?string $moduleName = 'Blog';

    protected ?string $routeName = 'BlogRedirect';

    /** @var array<string, mixed> */
    public array $bulkSheetRouteConfig = [];

    /** @var array<string, string> */
    public array $bulkSheetUiStrings = [];

    public function __construct(?Application $app = null)
    {
        $this->app = $app ?? app();
    }

    public function bulkSheetFields(): array
    {
        return [
            ['key' => 'from_path', 'label' => 'From', 'required' => true, 'aliases' => ['from']],
            ['key' => 'to_path', 'label' => 'To', 'required' => false, 'aliases' => []],
        ];
    }

    public function bulkSheetPrepareAndValidateRows(array $records): array
    {
        return $records;
    }

    public function bulkSheetCommitPreparedRows(array $prepared): array
    {
        return ['created' => count($prepared), 'updated' => 0];
    }

    public function bulkSheetStreamExport($resource): void
    {
        fputcsv($resource, ['from_path', 'to_path']);
    }

    protected function bulkSheetRouteConfig(): array
    {
        if (! $this->module) {
            return [];
        }

        return $this->bulkSheetRouteConfig;
    }

    protected function bulkSheetInertiaUiStrings(): array
    {
        return $this->bulkSheetUiStrings;
    }

    public function shareInertiaStoreVariables(): void
    {
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function getInertiaMainConfiguration(array $data): array
    {
        return $data['_mainConfiguration'] ?? [];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function getHeadLayoutData(array $data): array
    {
        return [];
    }

    public function invokeSetTableActionsManageBulkSheet(): void
    {
        $this->setTableActionsManageBulkSheet();
    }

    public function invokeAssertBulkSheetToolKey(string $toolKey): void
    {
        $this->assertBulkSheetToolKey($toolKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function invokeBulkSheetUiPropsForInertia(): array
    {
        return $this->bulkSheetUiPropsForInertia();
    }
}
