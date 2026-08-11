<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console\Blueprint;

use Illuminate\Filesystem\Filesystem;
use Unusualify\Modularous\Console\Blueprint\BlueprintClassWriter;
use Unusualify\Modularous\Console\Blueprint\BlueprintFieldCatalog;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestModulesCase;

class BlueprintScaffoldTest extends TestModulesCase
{
    protected function setUp(): void
    {
        parent::setUp();

        IsolatedTestModules::seedRoutesStatuses([
            'TestModule' => ['Item' => true],
            'SystemModule' => ['Item' => true],
        ]);
    }

    /** @test */
    public function it_normalizes_field_aliases(): void
    {
        $this->assertSame('columns', BlueprintFieldCatalog::normalize('headers'));
        $this->assertSame('columns', BlueprintFieldCatalog::normalize('index.columns'));
        $this->assertSame('inputs', BlueprintFieldCatalog::normalize('form.inputs'));
        $this->assertSame('options', BlueprintFieldCatalog::normalize('table_options'));
        $this->assertSame('form_actions', BlueprintFieldCatalog::normalize('form.actions'));
        $this->assertNull(BlueprintFieldCatalog::normalize('nope'));
    }

    /** @test */
    public function it_writes_prefixed_blueprint_classes_from_stubs(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $writer = new BlueprintClassWriter(new Filesystem);

        $path = $writer->write($module, 'Item', 'columns', [
            ['title' => 'Name', 'key' => 'name'],
        ], true);

        $this->assertNotNull($path);
        $this->assertFileExists($path);
        $this->assertStringContainsString('ItemIndexColumns', (string) file_get_contents($path));
        $this->assertStringContainsString(
            'TestModules\\TestModule\\Blueprint\\Item\\Index',
            (string) file_get_contents($path)
        );
        $this->assertSame(
            'TestModules\\TestModule\\Blueprint\\Item\\Index\\ItemIndexColumns',
            $writer->fqcn($module, 'Item', 'columns')
        );
    }
}
