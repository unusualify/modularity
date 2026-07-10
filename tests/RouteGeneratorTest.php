<?php

namespace Unusualify\Modularous\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JoeDixon\Translation\Drivers\Translation;
use Mockery;
use Unusualify\Modularous\Generators\RouteGenerator;
use Unusualify\Modularous\Tests\Repositories\TestModel;

class RouteGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected RouteGenerator $routeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeGenerator = with(new RouteGenerator('Test'))
            ->setFilesystem($this->app['files'])
            ->setConfig($this->app['config'])
            ->setModule('SystemPayment');
    }

    public function test_get_name_method(): void
    {
        $this->assertSame('Test', $this->routeGenerator->getName());
    }

    public function test_setters_and_getters_cover_configuration_branches(): void
    {
        $console = Mockery::mock(\Illuminate\Console\Command::class);

        $generator = $this->routeGenerator
            ->setFix(true)
            ->setType('api')
            ->setRoute('Payment')
            ->setForce(true)
            ->setMigrate(false)
            ->setMigration(false)
            ->setUseDefaults(false)
            ->setPlain(true)
            ->setSchema('name:string')
            ->setRules('name=required')
            ->setRelationships('company:belongsTo')
            ->setCustomModel(TestModel::class)
            ->setTraits(Collection::make(['soft-delete' => true]))
            ->setTableName('custom_payments')
            ->setConsole($console);

        $this->assertTrue($generator->getFix());
        $this->assertSame('Payment', $generator->getRoute());
        $this->assertSame(TestModel::class, $generator->getCustomModel());
        $this->assertSame('custom_payments', $generator->getTableName());
        $this->assertSame($console, $generator->getConsole());
        $this->assertTrue($generator->getTraits()->has('soft-delete'));
    }

    public function test_schema_parser_helpers_derive_model_metadata(): void
    {
        $generator = $this->routeGenerator
            ->setSchema('name:string,deleted_at:datetime')
            ->setRelationships('owner:belongsTo')
            ->setUseDefaults(true);

        $this->assertContains('name', $generator->getModelFillables());
        $this->assertNotEmpty($generator->getHeaders());
        $this->assertNotEmpty($generator->getInputs());
        $this->assertIsBool($generator->hasSoftDelete());
        $this->assertNotEmpty($generator->getModelRelationships());
    }

    public function test_replace_string_substitutes_generator_tokens(): void
    {
        $replaced = $this->routeGenerator->replaceString(
            'modules/$STUDLY_NAME$/Repositories/$SNAKE_CASE$Repository.php'
        );

        $this->assertSame('modules/Test/Repositories/testRepository.php', $replaced);
    }

    public function test_get_replacements_reads_generator_config(): void
    {
        $replacements = $this->routeGenerator->getReplacements();

        $this->assertIsArray($replacements);
        $this->assertArrayHasKey('composer', $replacements);
    }

    public function test_fix_config_file_merges_missing_route_metadata(): void
    {
        $configPath = $this->routeGenerator->getModule()->getConfigPath();
        $backup = file_get_contents($configPath);

        try {
            $this->routeGenerator
                ->setFix(true)
                ->setSchema('name:string')
                ->fixConfigFile();

            $config = include $configPath;

            $this->assertArrayHasKey('routes', $config);
            $this->assertArrayHasKey('test', $config['routes']);
            $this->assertSame('Test', $config['routes']['test']['name']);
        } finally {
            file_put_contents($configPath, $backup);
        }
    }

    public function test_add_language_variable_writes_translation_groups(): void
    {
        $translation = Mockery::mock(Translation::class);
        $translation->shouldReceive('addGroupTranslation')->atLeast()->once();
        $translation->shouldReceive('allLanguages')->andReturn(['en', 'tr']);

        $this->routeGenerator->setTranslation($translation);

        $this->assertTrue($this->routeGenerator->addLanguageVariable());
    }

    public function test_create_route_permissions_is_safe_when_repository_missing(): void
    {
        $this->assertTrue($this->routeGenerator->createRoutePermissions());
    }

    public function test_generator_config_reads_module_generator_paths(): void
    {
        $path = $this->routeGenerator->generatorConfig('repository');

        $this->assertNotEmpty($path->getPath());
    }

    public function test_update_config_file_writes_route_metadata_when_missing(): void
    {
        $configPath = $this->routeGenerator->getModule()->getConfigPath();
        $backup = file_get_contents($configPath);

        try {
            $this->routeGenerator
                ->setSchema('title:string')
                ->setUseDefaults(true);

            $this->assertTrue((bool) $this->routeGenerator->updateConfigFile());

            $config = include $configPath;
            $this->assertArrayHasKey('test', $config['routes']);
            $this->assertArrayHasKey('headers', $config['routes']['test']);
            $this->assertArrayHasKey('inputs', $config['routes']['test']);
        } finally {
            file_put_contents($configPath, $backup);
        }
    }

    public function test_update_routes_statuses_invokes_module_route_enablement(): void
    {
        $module = Mockery::mock($this->routeGenerator->getModule())->makePartial();
        $module->shouldReceive('enableRoute')->once()->with('Test');

        \Unusualify\Modularous\Facades\Modularous::shouldReceive('findOrFail')
            ->once()
            ->andReturn($module);

        $ref = new \ReflectionProperty(RouteGenerator::class, 'module');
        $ref->setAccessible(true);
        $ref->setValue($this->routeGenerator, $module);

        $this->routeGenerator->updateRoutesStatuses();
    }

    public function test_generate_extra_migrations_handles_morphed_by_many(): void
    {
        $console = Mockery::mock(\Illuminate\Console\Command::class);
        $console->shouldReceive('call')->atLeast()->once();

        $module = Mockery::mock($this->routeGenerator->getModule())->makePartial();
        $module->shouldReceive('isFileExists')->andReturn(false);
        $module->shouldReceive('getStudlyName')->andReturn('SystemPayment');

        $generator = (new RouteGenerator('Test'))
            ->setFilesystem($this->app['files'])
            ->setConfig($this->app['config'])
            ->setConsole($console)
            ->setRelationships('tags:morphedByMany');

        $ref = new \ReflectionProperty(RouteGenerator::class, 'module');
        $ref->setAccessible(true);
        $ref->setValue($generator, $module);

        $this->assertTrue($generator->generateExtraMigrations());
    }

    public function test_get_replacement_resolves_composer_keys(): void
    {
        $this->app['config']->set('modularous.stubs.replacements', [
            'composer' => ['VENDOR', 'AUTHOR', 'AUTHOR_EMAIL', 'MODULE_NAMESPACE'],
        ]);
        $this->app['config']->set('modularous.composer.vendor', 'acme');
        $this->app['config']->set('modularous.composer.author.name', 'Jane');
        $this->app['config']->set('modularous.composer.author.email', 'jane@example.com');
        $this->app['config']->set('modules.namespace', 'Modules\\Acme');

        $method = new \ReflectionMethod(RouteGenerator::class, 'getReplacement');
        $method->setAccessible(true);
        $replacement = $method->invoke($this->routeGenerator, 'composer');

        $this->assertSame('acme', $replacement['VENDOR']);
        $this->assertSame('Jane', $replacement['AUTHOR']);
        $this->assertSame('jane@example.com', $replacement['AUTHOR_EMAIL']);
        $this->assertStringContainsString('Modules', $replacement['MODULE_NAMESPACE']);
    }

    public function test_generate_runs_test_workflow_without_writing_files(): void
    {
        $console = Mockery::mock(\Illuminate\Console\Command::class);
        $console->shouldReceive('call')->atLeast()->once();
        $console->shouldReceive('info')->atLeast()->once();

        $module = Mockery::mock($this->routeGenerator->getModule())->makePartial();
        $module->shouldReceive('getRawRouteConfig')->with('Test')->andReturn(null);
        $module->shouldReceive('getStudlyName')->andReturn('SystemPayment');
        $module->shouldReceive('isFileExists')->andReturn(true);

        $generator = $this->routeGenerator
            ->setConsole($console)
            ->setSchema('name:string')
            ->setTest(true);

        $ref = new \ReflectionProperty(RouteGenerator::class, 'module');
        $ref->setAccessible(true);
        $ref->setValue($generator, $module);

        $this->assertSame(0, $generator->generate());
    }

    public function test_generate_git_keep_writes_empty_file(): void
    {
        $path = sys_get_temp_dir() . '/route-generator-gitkeep-' . uniqid();
        mkdir($path, 0755, true);

        try {
            $this->routeGenerator->generateGitKeep($path);
            $this->assertFileExists($path . '/.gitkeep');
            $this->assertSame('', file_get_contents($path . '/.gitkeep'));
        } finally {
            @unlink($path . '/.gitkeep');
            @rmdir($path);
        }
    }
}
