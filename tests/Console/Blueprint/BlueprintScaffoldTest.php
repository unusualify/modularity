<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console\Blueprint;

use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Unusualify\Modularous\Console\Blueprint\BlueprintClassWriter;
use Unusualify\Modularous\Console\Blueprint\BlueprintConfigPersister;
use Unusualify\Modularous\Console\Blueprint\BlueprintConfigSourceExtractor;
use Unusualify\Modularous\Console\Blueprint\BlueprintFieldCatalog;
use Unusualify\Modularous\Console\Blueprint\BlueprintPhpArrayFormatter;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Generators\RouteGenerator;
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
        $this->assertSame($path, $writer->path($module, 'Item', 'columns'));
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

    /** @test */
    public function it_exports_short_array_syntax_without_numeric_keys(): void
    {
        $code = BlueprintPhpArrayFormatter::export([
            ['title' => 'Subject', 'key' => 'subject'],
            'bulk-mark-read' => [
                'label' => 'Mark All as Read',
            ],
        ]);

        $this->assertStringStartsWith('[', $code);
        $this->assertStringNotContainsString('array (', $code);
        $this->assertStringNotContainsString('0 =>', $code);
        $this->assertStringContainsString("'title' => 'Subject'", $code);
        $this->assertStringContainsString("'bulk-mark-read' =>", $code);
    }

    /** @test */
    public function it_extracts_raw_config_source_preserving_translation_helpers(): void
    {
        $tmp = sys_get_temp_dir() . '/modularous-blueprint-extract-' . uniqid() . '.php';
        file_put_contents($tmp, <<<'PHP'
<?php

return [
    'routes' => [
        'my_notification' => [
            'table_actions' => [
                'bulk-mark-read' => [
                    'label' => __('Mark All as Read'),
                    'forceLabel' => true,
                ],
            ],
            // 'table_actions' => [
            //     'label' => __('Ignored'),
            // ],
        ],
    ],
];
PHP);

        try {
            $extractor = new BlueprintConfigSourceExtractor;
            $literal = $extractor->extract($tmp, 'my_notification', 'table_actions', 'index.actions');

            $this->assertNotNull($literal);
            $this->assertStringContainsString("__('Mark All as Read')", (string) $literal);
            $this->assertStringContainsString("'bulk-mark-read'", (string) $literal);
            $this->assertStringNotContainsString('Ignored', (string) $literal);
            $this->assertStringNotContainsString('array (', (string) $literal);
        } finally {
            @unlink($tmp);
        }
    }

    /** @test */
    public function it_normalizes_bulk_sheet_aliases(): void
    {
        $this->assertSame('bulk_sheet', BlueprintFieldCatalog::normalize('bulk_sheet'));
        $this->assertSame('bulk_sheet', BlueprintFieldCatalog::normalize('bulk'));
        $this->assertSame('BulkSheet', BlueprintFieldCatalog::get('bulk_sheet')['surface']);
        $this->assertSame('bulk_sheet', BlueprintFieldCatalog::get('bulk_sheet')['nested_key']);
    }

    /** @test */
    public function it_persists_root_bulk_sheet_class_leaf(): void
    {
        $tmp = sys_get_temp_dir() . '/modularous-blueprint-bulk-' . uniqid() . '.php';
        file_put_contents($tmp, <<<'PHP'
<?php

return [
    'routes' => [
        'redirect' => [
            'name' => 'Redirect',
            'bulk_sheet' => [
                'export_download_filename' => 'redirects-export.csv',
            ],
        ],
    ],
];
PHP);

        try {
            $persister = new BlueprintConfigPersister;
            $result = $persister->persist($tmp, 'redirect', [
                [
                    'nested' => 'bulk_sheet',
                    'fqcn' => 'Modules\\Cms\\Blueprint\\Redirect\\BulkSheet\\RedirectBulkSheet',
                    'legacy' => 'bulk_sheet',
                ],
            ]);

            $this->assertTrue($result['ok']);
            $content = (string) file_get_contents($tmp);
            $this->assertStringContainsString(
                'RedirectBulkSheet::class',
                $content
            );
            $this->assertStringNotContainsString("'export_download_filename'", $content);
        } finally {
            @unlink($tmp);
        }
    }

    /** @test */
    public function it_comments_nested_flats_with_aligned_slashes(): void
    {
        $tmp = sys_get_temp_dir() . '/modularous-blueprint-comment-align-' . uniqid() . '.php';
        file_put_contents($tmp, <<<'PHP'
<?php

return [
    'routes' => [
        'sitemap' => [
            'name' => 'Sitemap',
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Status',
                    'key' => 'published',
                    'formatter' => [
                        'switch',
                    ],
                ],
            ],
        ],
    ],
];
PHP);

        try {
            $persister = new BlueprintConfigPersister;
            $result = $persister->persist($tmp, 'sitemap', [
                [
                    'nested' => 'index.columns',
                    'fqcn' => 'Modules\\Cms\\Blueprint\\Sitemap\\Index\\SitemapIndexColumns',
                    'legacy' => 'headers',
                ],
            ]);

            $this->assertTrue($result['ok']);
            $content = (string) file_get_contents($tmp);

            $this->assertStringContainsString("            // 'headers' => [", $content);
            $this->assertStringContainsString('            //     [', $content);
            $this->assertStringContainsString("            //         'title' => 'Name',", $content);
            $this->assertStringContainsString("            //             'edit',", $content);
            $this->assertStringNotContainsString('                // [', $content);
        } finally {
            @unlink($tmp);
        }
    }

    /** @test */
    public function it_persists_nested_blueprint_leaves_and_comments_legacy_flats(): void
    {
        $tmp = sys_get_temp_dir() . '/modularous-blueprint-persist-' . uniqid() . '.php';
        file_put_contents($tmp, <<<'PHP'
<?php

return [
    'routes' => [
        'general' => [
            'name' => 'General',
            'inputs' => [
                ['type' => 'text', 'name' => 'host'],
            ],
        ],
    ],
];
PHP);

        try {
            $persister = new BlueprintConfigPersister;
            $result = $persister->persist($tmp, 'general', [
                [
                    'nested' => 'form.inputs',
                    'fqcn' => 'Modules\\SystemSetting\\Blueprint\\General\\Form\\GeneralFormInputs',
                    'legacy' => 'inputs',
                ],
            ]);

            $this->assertTrue($result['ok']);
            $content = (string) file_get_contents($tmp);
            $this->assertStringContainsString("'form' => [", $content);
            $this->assertStringContainsString(
                'GeneralFormInputs::class',
                $content
            );
            $this->assertStringContainsString("// 'inputs' => [", $content);
            $this->assertStringNotContainsString('array (', $content);
            $this->assertContains('inputs', $result['commented_flats']);
        } finally {
            @unlink($tmp);
        }
    }

    /** @test */
    public function it_dry_runs_make_blueprint_without_writing_files(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $writer = new BlueprintClassWriter(new Filesystem);
        $target = $writer->path($module, 'Item', 'inputs');

        if (is_file($target)) {
            unlink($target);
        }

        $this->artisan('modularous:make:blueprint', [
            'module' => 'TestModule',
            'route' => 'Item',
            '--only' => 'inputs',
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('[dry-run]')
            ->expectsOutputToContain('create')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist($target);
    }

    /** @test */
    public function it_skips_blueprint_driver_meta_when_extracting_payload_source(): void
    {
        $tmp = sys_get_temp_dir() . '/modularous-blueprint-meta-skip-' . uniqid() . '.php';
        file_put_contents($tmp, <<<'PHP'
<?php

return [
    'routes' => [
        'test' => [
            'blueprint' => [
                'inputs' => [
                    'driver' => 'class',
                    'class' => 'Modules\\Test\\Blueprint\\Test\\Form\\TestFormInputs',
                ],
                'headers' => [
                    'driver' => 'class',
                    'class' => 'Modules\\Test\\Blueprint\\Test\\Index\\TestIndexColumns',
                ],
            ],
            'headers' => [
                ['title' => 'Name', 'key' => 'name'],
            ],
            'inputs' => [
                ['type' => 'text', 'name' => 'name'],
            ],
        ],
    ],
];
PHP);

        try {
            $extractor = new BlueprintConfigSourceExtractor;
            $inputs = $extractor->extract($tmp, 'test', 'inputs', 'form.inputs');
            $headers = $extractor->extract($tmp, 'test', 'headers', 'index.columns');

            $this->assertNotNull($inputs);
            $this->assertStringContainsString("'name' => 'name'", (string) $inputs);
            $this->assertStringNotContainsString("'driver'", (string) $inputs);

            $this->assertNotNull($headers);
            $this->assertStringContainsString("'key' => 'name'", (string) $headers);
            $this->assertStringNotContainsString("'driver'", (string) $headers);
        } finally {
            @unlink($tmp);
        }
    }

    /** @test */
    public function route_generator_scaffolds_blueprint_payloads_and_nested_class_leaves(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $configPath = $module->getConfigPath();
        $backup = (string) file_get_contents($configPath);

        $console = Mockery::mock(Console::class);
        $console->shouldReceive('info')->zeroOrMoreTimes();
        $console->shouldReceive('warn')->zeroOrMoreTimes();
        $console->shouldReceive('line')->zeroOrMoreTimes();

        $filesystem = new Filesystem;
        $writer = new BlueprintClassWriter($filesystem);

        $paths = [
            $writer->path($module, 'Item', 'inputs'),
            $writer->path($module, 'Item', 'columns'),
            $writer->path($module, 'Item', 'options'),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        try {
            $generator = with(new RouteGenerator('Item'))
                ->setFilesystem($filesystem)
                ->setConfig($this->app['config'])
                ->setConsole($console)
                ->setModule('TestModule')
                ->setSchema('name:string')
                ->setUseDefaults(false)
                ->setPresentation('class')
                ->setForce(true);

            $generator->generatePresentationClasses();

            foreach ($paths as $path) {
                $this->assertFileExists($path);
                $body = (string) file_get_contents($path);
                $this->assertStringNotContainsString("'driver'", $body);
                $this->assertStringNotContainsString("'class' =>", $body);
            }

            $inputsBody = (string) file_get_contents($paths[0]);
            $this->assertStringContainsString("'name'", $inputsBody);

            $columnsBody = (string) file_get_contents($paths[1]);
            $this->assertStringContainsString("'key'", $columnsBody);

            $optionsBody = (string) file_get_contents($paths[2]);
            $this->assertStringContainsString("'createOnModal'", $optionsBody);

            $config = (string) file_get_contents($configPath);
            $this->assertStringContainsString('ItemIndexColumns::class', $config);
            $this->assertStringContainsString('ItemIndexOptions::class', $config);
            $this->assertStringContainsString('ItemFormInputs::class', $config);
            $this->assertStringContainsString("'index' => [", $config);
            $this->assertStringContainsString("'form' => [", $config);
            $this->assertStringNotContainsString("'blueprint' =>", $config);
        } finally {
            file_put_contents($configPath, $backup);
            foreach ($paths as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    /** @test */
    public function route_generator_update_config_wires_nested_fqcn_without_legacy_blueprint_meta(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $configPath = $module->getConfigPath();
        $backup = (string) file_get_contents($configPath);

        // Start from a config without the target route so updateConfigFile appends it.
        file_put_contents($configPath, <<<'PHP'
<?php

return [
    'name' => 'TestModule',
    'system_prefix' => false,
    'group' => 'test',
    'headline' => 'Test Module',
    'routes' => [
    ],
];
PHP);

        $console = Mockery::mock(Console::class);
        $console->shouldReceive('info')->zeroOrMoreTimes();
        $console->shouldReceive('warn')->zeroOrMoreTimes();
        $console->shouldReceive('line')->zeroOrMoreTimes();

        try {
            $generator = with(new RouteGenerator('Widget'))
                ->setFilesystem(new Filesystem)
                ->setConfig($this->app['config'])
                ->setConsole($console)
                ->setModule('TestModule')
                ->setSchema('name:string')
                ->setUseDefaults(false)
                ->setPresentation('class');

            $this->assertTrue((bool) $generator->updateConfigFile());

            $config = (string) file_get_contents($configPath);
            $this->assertStringContainsString("'widget'", $config);
            $this->assertStringContainsString("'index'", $config);
            $this->assertStringContainsString("'form'", $config);
            $this->assertStringContainsString('WidgetIndexColumns', $config);
            $this->assertStringContainsString('WidgetFormInputs', $config);
            $this->assertStringContainsString('WidgetIndexOptions', $config);
            $this->assertStringNotContainsString("'blueprint'", $config);
            $this->assertStringNotContainsString("'driver'", $config);
            // Flats are not the primary wiring for class presentation scaffolds.
            $this->assertStringNotContainsString("'headers' =>", $config);
            $this->assertStringNotContainsString("'inputs' => [", $config);
            $this->assertStringNotContainsString("'table_options' =>", $config);
        } finally {
            file_put_contents($configPath, $backup);
        }
    }
}
