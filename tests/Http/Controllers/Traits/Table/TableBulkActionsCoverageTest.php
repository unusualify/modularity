<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\Table;

use Mockery;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Http\Controllers\Traits\Table\TableBulkActions;
use Unusualify\Modularous\Tests\TestCase;

class TableBulkActionsCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_builds_bulk_actions_from_index_options_when_module_present(): void
    {
        $module = Mockery::mock();
        $module->shouldReceive('generatePermissionMiddlewareDefinition')
            ->once()
            ->with(Permission::DELETE->value, 'Post')
            ->andReturn('can:delete-post');

        $controller = new class($module)
        {
            use TableBulkActions;

            public $module;

            public string $routeName = 'Post';

            public array $options = [
                'delete' => true,
                'forceDelete' => true,
                'restore' => true,
            ];

            public function __construct($module)
            {
                $this->module = $module;
            }

            protected function getIndexOption($option)
            {
                return $this->options[$option] ?? false;
            }

            public function callGetTableBulkActions(): array
            {
                return $this->getTableBulkActions();
            }
        };

        $actions = $controller->callGetTableBulkActions();
        $names = array_column($actions, 'name');

        $this->assertSame(['bulkDelete', 'bulkForceDelete', 'bulkRestore'], $names);
        $this->assertSame('can:delete-post', $actions[0]['can']);
        $this->assertSame('forceDelete', $actions[1]['can']);
        $this->assertSame('restore', $actions[2]['can']);
    }

    /** @test */
    public function it_returns_empty_without_module_or_when_options_disabled(): void
    {
        $controller = new class
        {
            use TableBulkActions;

            public $module = null;

            protected function getIndexOption($option)
            {
                return true;
            }

            public function callGetTableBulkActions(): array
            {
                return $this->getTableBulkActions();
            }
        };

        $this->assertSame([], $controller->callGetTableBulkActions());

        $module = Mockery::mock();
        $module->shouldReceive('generatePermissionMiddlewareDefinition')->never();

        $disabled = new class($module)
        {
            use TableBulkActions;

            public $module;

            public string $routeName = 'Post';

            public function __construct($module)
            {
                $this->module = $module;
            }

            protected function getIndexOption($option)
            {
                return false;
            }

            public function callGetTableBulkActions(): array
            {
                return $this->getTableBulkActions();
            }
        };

        $this->assertSame([], $disabled->callGetTableBulkActions());
    }
}
