<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\BulkCsv\BulkImportService;
use Unusualify\Modularous\Tests\Http\Controllers\ControllerUsingManageBulkSheet;
use Unusualify\Modularous\Tests\TestCase;

class ManageBulkSheetTest extends TestCase
{
    protected ControllerUsingManageBulkSheet $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingManageBulkSheet($this->app);
        $this->controller->module = $this->makeModuleMock();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_bulk_sheet_tool_key_defaults_from_module_and_route(): void
    {
        $this->assertSame('blog.blog_redirect', $this->controller->bulkSheetToolKey());
    }

    public function test_bulk_sheet_tool_key_uses_config_override(): void
    {
        $this->controller->bulkSheetRouteConfig = ['tool_key' => 'custom.tool'];

        $this->assertSame('custom.tool', $this->controller->bulkSheetToolKey());
    }

    public function test_bulk_sheet_export_download_filename_uses_config_or_default(): void
    {
        $this->assertSame('Blog-BlogRedirect-export.csv', $this->controller->bulkSheetExportDownloadFilename());

        $this->controller->bulkSheetRouteConfig = ['export_download_filename' => 'redirects.csv'];

        $this->assertSame('redirects.csv', $this->controller->bulkSheetExportDownloadFilename());
    }

    public function test_bulk_sheet_web_route_names_merge_config_over_defaults(): void
    {
        $this->controller->bulkSheetRouteConfig = [
            'web_route_names' => ['export' => 'bulk.export.custom'],
        ];

        $names = $this->controller->bulkSheetWebRouteNames();

        $this->assertSame('bulk.tool', $names['tool']);
        $this->assertSame('bulk.export.custom', $names['export']);
    }

    public function test_bulk_sheet_toolbar_definition_builds_action_href(): void
    {
        $definition = $this->controller->bulkSheetToolbarDefinition();

        $this->assertSame('admin.blog.blog_redirect.bulk.tool', $definition['href']);
        $this->assertSame('append', $definition['position']);
        $this->assertNotEmpty($definition['label']);
    }

    public function test_set_table_actions_manage_bulk_sheet_appends_toolbar_action(): void
    {
        $this->controller->tableActions = [['name' => 'existing']];

        $this->controller->invokeSetTableActionsManageBulkSheet();

        $this->assertCount(2, $this->controller->tableActions);
        $this->assertSame('existing', $this->controller->tableActions[0]['name']);
        $this->assertArrayHasKey('bulkTool', $this->controller->tableActions[1]);
        $this->assertSame('blog.blog_redirect', $this->controller->tableActions[1]['bulkTool']['toolKey']);
    }

    public function test_set_table_actions_manage_bulk_sheet_prepends_when_configured(): void
    {
        $this->controller->bulkSheetRouteConfig = ['toolbar_position' => 'prepend'];
        $this->controller->tableActions = [['name' => 'existing']];

        $this->controller->invokeSetTableActionsManageBulkSheet();

        $this->assertArrayHasKey('bulkTool', $this->controller->tableActions[0]);
        $this->assertSame('existing', $this->controller->tableActions[1]['name']);
    }

    public function test_set_table_actions_manage_bulk_sheet_skips_without_module(): void
    {
        $this->controller->module = null;
        $this->controller->tableActions = [['name' => 'existing']];

        $this->controller->invokeSetTableActionsManageBulkSheet();

        $this->assertCount(1, $this->controller->tableActions);
    }

    public function test_bulk_sheet_step_up_ability_returns_config_value(): void
    {
        $this->assertNull($this->controller->bulkSheetStepUpAbility());

        $this->controller->bulkSheetRouteConfig = ['step_up_ability' => 'redirect.bulk_import'];

        $this->assertSame('redirect.bulk_import', $this->controller->bulkSheetStepUpAbility());
    }

    public function test_bulk_sheet_tool_headline_and_preview_columns_use_config(): void
    {
        $this->controller->bulkSheetRouteConfig = [
            'tool_headline' => 'Bulk redirects',
            'preview_table_columns' => [['title' => 'From', 'key' => 'from_path']],
        ];

        $this->assertSame('Bulk redirects', $this->controller->bulkSheetToolHeadline());
        $this->assertCount(1, $this->controller->bulkSheetPreviewTableColumns());
    }

    public function test_bulk_sheet_ui_props_for_inertia_merges_intro(): void
    {
        $this->controller->bulkSheetUiStrings = ['columns' => 'Columns'];
        $this->controller->bulkSheetRouteConfig = ['toolbar_intro' => 'Import CSV rows'];

        $props = $this->controller->invokeBulkSheetUiPropsForInertia();

        $this->assertSame('Import CSV rows', $props['intro']);
        $this->assertSame('Columns', $props['columns']);
    }

    public function test_assert_bulk_sheet_tool_key_aborts_on_mismatch(): void
    {
        try {
            $this->controller->invokeAssertBulkSheetToolKey('wrong.key');
            $this->fail('Expected HttpException with status 403.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_bulk_sheet_dry_run_returns_import_preview(): void
    {
        Route::shouldReceive('has')->andReturn(false);

        $bulk = Mockery::mock(BulkImportService::class);
        $bulk->shouldReceive('import')
            ->once()
            ->with('a,b', true, $this->controller, 'blog.blog_redirect')
            ->andReturn(['valid' => 1, 'rows' => []]);
        $this->app->instance(BulkImportService::class, $bulk);

        $response = $this->controller->bulkSheetDryRun(new Request([
            'csv' => 'a,b',
        ]));

        $this->assertSame(['valid' => 1, 'rows' => []], $response->getData(true));
    }

    public function test_bulk_sheet_commit_runs_import(): void
    {
        Route::shouldReceive('has')->andReturn(false);

        $bulk = Mockery::mock(BulkImportService::class);
        $bulk->shouldReceive('import')
            ->once()
            ->with('a,b', false, $this->controller, 'blog.blog_redirect')
            ->andReturn(['created' => 1, 'updated' => 0]);
        $this->app->instance(BulkImportService::class, $bulk);

        $response = $this->controller->bulkSheetCommit(new Request([
            'csv' => 'a,b',
        ]));

        $this->assertSame(['created' => 1, 'updated' => 0], $response->getData(true));
    }

    protected function makeModuleMock(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('panelRouteNamePrefix')->andReturn('admin.blog');
        $module->shouldReceive('getRawRouteConfig')->andReturn([]);

        return $module;
    }
}
