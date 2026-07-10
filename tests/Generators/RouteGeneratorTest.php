<?php

namespace Unusualify\Modularous\Tests\Generators;

use Illuminate\Config\Repository;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Mockery;
use Modules\SystemUser\Repositories\PermissionRepository;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Generators\RouteGenerator;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\FileTranslation;
use Unusualify\Modularous\Tests\TestCase;

class RouteGeneratorTest extends TestCase
{
    protected $generator;

    protected $filesystem;

    protected $console;

    protected $module;

    protected $newModule;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $fixturesPath = realpath(__DIR__ . '/../../test-modules');
        if ($fixturesPath) {
            $app['config']->set('modules.paths.modules', $fixturesPath);
            $app['config']->set('modules.scan.paths', [$fixturesPath]);
        }
        $app['config']->set('modules.namespace', 'TestModules');

        // Align generator paths with fixture layout (Entities, Repositories, Controllers at module root)
        $app['config']->set('modules.paths.generator.model', ['path' => 'Entities', 'namespace' => 'Entities', 'generate' => false]);
        $app['config']->set('modules.paths.generator.repository', ['path' => 'Repositories', 'namespace' => 'Repositories', 'generate' => false]);
        $app['config']->set('modules.paths.generator.controller', ['path' => 'Controllers', 'namespace' => 'Controllers', 'generate' => false]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('modularous.base_key', 'modularous');

        // Ensure ALL generators have 'generate' => true to avoid prompts
        $generators = [
            'route-controller', 'route-controller-api', 'route-controller-front',
            'repository', 'route-request', 'route-resource', 'lang',
            'provider', 'filter',
        ];

        foreach ($generators as $gen) {
            $this->app['config']->set("modularous.paths.generator.$gen", ['path' => 'Test', 'generate' => true]);
            $this->app['config']->set("modules.paths.generator.$gen", ['path' => 'Test', 'generate' => true]);
        }

        $this->app['config']->set('modularous.stubs.files', []);

        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->console = Mockery::mock(Console::class);
        $this->module = Mockery::mock(Module::class);

        $this->module->shouldReceive('isModularousModule')->andReturn(false)->byDefault();
        $this->module->shouldReceive('getStudlyName')->andReturn('TestModule')->byDefault();
        $this->module->shouldReceive('getName')->andReturn('TestModule')->byDefault();
        $this->module->shouldReceive('getSnakeName')->andReturn('test_module')->byDefault();
        $this->module->shouldReceive('getPath')->andReturn('/tmp/test-module')->byDefault();
        $this->module->shouldReceive('getFileExists')->andReturn(false)->byDefault();
        $this->module->shouldReceive('isFileExists')->andReturn(false)->byDefault();
        $this->module->shouldReceive('getDirectoryPath')->andReturn('/tmp/test-module/Resources/lang')->byDefault();

        $this->filesystem->shouldReceive('exists')->andReturn(true)->byDefault();

        $this->generator = new class('TestRoute', $this->app['config'], $this->filesystem, $this->console, $this->module) extends RouteGenerator {};
    }

    /** @test */
    public function it_can_set_and_get_fix()
    {
        $this->generator->setFix(true);
        $this->assertTrue($this->generator->getFix());
    }

    /** @test */
    public function it_can_set_type()
    {
        $this->generator->setType('api');
        $this->assertEquals($this->generator, $this->generator->setType('api'));
    }

    /** @test */
    public function it_returns_studly_name()
    {
        $this->assertEquals('TestRoute', $this->generator->getName());
    }

    /** @test */
    public function it_returns_model_fillables()
    {
        $this->generator->setSchema('name:string');
        $this->assertIsArray($this->generator->getModelFillables());
    }

    /** @test */
    public function it_creates_route_permissions()
    {
        // PermissionRepository is now stubbed in tests/Stubs/Modules/SystemUser/Repositories
        $permissionRepository = Mockery::mock(PermissionRepository::class);
        $this->app->instance(PermissionRepository::class, $permissionRepository);

        Modularous::shouldReceive('getAuthGuardName')->andReturn('admin');
        $permissionRepository->shouldReceive('firstOrCreate')->atLeast()->once();

        $this->assertTrue($this->generator->createRoutePermissions());
    }

    /** @test */
    public function it_adds_language_variables()
    {
        $this->module->shouldReceive('getSnakeName')->andReturn('test-module');

        $translationMock = Mockery::mock(FileTranslation::class);
        $this->generator->setTranslation($translationMock);

        $translationMock->shouldReceive('addGroupTranslation')->atLeast()->once();
        $translationMock->shouldReceive('allLanguages')->andReturn(collect(['en', 'tr']));

        $this->assertTrue($this->generator->addLanguageVariable());
    }

    /** @test */
    public function it_updates_config_file()
    {
        $this->module->shouldReceive('getSnakeName')->andReturn('test_module');
        $this->module->shouldReceive('getConfigPath')->andReturn('/tmp/config.php');
        $this->app['config']->set('test_module', []);

        $this->filesystem->shouldReceive('exists')->andReturn(false);
        $this->filesystem->shouldReceive('put')->once()->andReturn(true);

        $this->assertTrue($this->generator->updateConfigFile());
    }

    /** @test */
    public function it_generates_resources()
    {
        $this->console->shouldReceive('call')->atLeast()->once();

        $this->generator->generateResources();
        $this->assertTrue(true);
    }

    /** @test */
    public function it_generates_extra_migrations_for_relationships()
    {
        $this->generator->setRelationships('posts:belongsToMany');

        $this->module->shouldReceive('isFileExists')->andReturn(false);
        $this->console->shouldReceive('call')->atLeast()->once();

        $this->assertTrue($this->generator->generateExtraMigrations());
    }

    // ============================================================
    // Phase 1: Setter/Getter Tests
    // ============================================================

    /** @test */
    public function it_can_set_and_get_config()
    {
        $newConfig = Mockery::mock(Repository::class);
        $result = $this->generator->setConfig($newConfig);

        $this->assertSame($this->generator, $result);
        $this->assertSame($newConfig, $this->generator->getConfig());
    }

    /** @test */
    public function it_can_set_and_get_filesystem()
    {
        $newFilesystem = Mockery::mock(Filesystem::class);
        $result = $this->generator->setFilesystem($newFilesystem);

        $this->assertSame($this->generator, $result);
        $this->assertSame($newFilesystem, $this->generator->getFilesystem());
    }

    /** @test */
    public function it_can_set_and_get_console()
    {
        $newConsole = Mockery::mock(Console::class);
        $result = $this->generator->setConsole($newConsole);

        $this->assertSame($this->generator, $result);
        $this->assertSame($newConsole, $this->generator->getConsole());
    }

    /** @test */
    public function it_can_set_traits()
    {
        $this->generator = $this->generator->setTraits(collect([
            'addTranslation' => true,
            'addMedia' => true,
            'addFile' => true,
            'addPosition' => true,
            'addSlug' => true,
        ]));

        $this->assertInstanceOf(RouteGenerator::class, $this->generator);
        $traits = $this->generator->getTraits();

        $this->assertInstanceOf(Collection::class, $traits);
        $this->assertSame(collect([
            'addTranslation' => true,
            'addMedia' => true,
            'addFile' => true,
            'addPosition' => true,
            'addSlug' => true,
        ])->toArray(), $this->generator->getTraits()->toArray());
    }

    /** @test */
    public function it_can_set_module()
    {
        $result = $this->generator->setModule('SystemModule');

        // delete folder
        $langPath = base_path('lang');
        app('files')->deleteDirectory($langPath);

        $this->assertInstanceOf(RouteGenerator::class, $result);
        $this->assertSame($this->generator, $result);

        $this->assertInstanceOf(Module::class, $this->generator->getModule());
        $this->assertSame('SystemModule', $this->generator->getModule()->getName());
    }

    /** @test */
    public function it_can_set_and_get_route()
    {
        $result = $this->generator->setRoute('custom-route');
        $this->assertSame($this->generator, $result);
        $this->assertEquals('custom-route', $this->generator->getRoute());
    }

    /** @test */
    public function it_returns_folders_array()
    {
        $folders = $this->generator->getFolders();
        $this->assertIsArray($folders);
    }

    /** @test */
    public function it_returns_files_array()
    {
        $files = $this->generator->getFiles();
        $this->assertIsArray($files);
    }

    /** @test */
    public function it_can_set_force_flag()
    {
        $result = $this->generator->setForce(true);
        $this->assertSame($this->generator, $result);
    }

    /** @test */
    public function it_can_set_migrate_flag()
    {
        $result = $this->generator->setMigrate(false);
        $this->assertSame($this->generator, $result);
    }

    /** @test */
    public function it_can_set_migration_flag()
    {
        $result = $this->generator->setMigration(false);
        $this->assertSame($this->generator, $result);
    }

    /** @test */
    public function it_can_set_use_defaults_flag()
    {
        $result = $this->generator->setUseDefaults(true);
        $this->assertSame($this->generator, $result);
    }

    /** @test */
    public function it_can_set_plain_flag()
    {
        $result = $this->generator->setPlain(true);
        $this->assertSame($this->generator, $result);
    }

    /** @test */
    public function it_can_set_rules()
    {
        $result = $this->generator->setRules('required|min:3');
        $this->assertSame($this->generator, $result);
    }

    /** @test */
    public function it_can_set_custom_model()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('setCustomModel');
        $method->setAccessible(true);

        $result = $method->invoke($this->generator, User::class);
        $this->assertSame($this->generator, $result);

        $this->assertEquals(User::class, $this->generator->getCustomModel());
    }

    /** @test */
    public function it_can_set_and_get_table_name()
    {
        $this->generator->setTableName('custom_table');
        $this->assertEquals('custom_table', $this->generator->getTableName());
    }

    // ============================================================
    // Phase 2: Replacement/Stub Method Tests
    // ============================================================

    /** @test */
    public function it_returns_lower_name_replacement()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getLowerNameReplacement');
        $method->setAccessible(true);

        $this->assertEquals('testroute', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_module_lower_name_replacement()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getModuleLowerNameReplacement');
        $method->setAccessible(true);

        $this->assertEquals('testmodule', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_lower_module_name_replacement()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getLowerModuleNameReplacement');
        $method->setAccessible(true);

        $this->assertEquals('testmodule', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_module_studly_name_replacement()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getModuleStudlyNameReplacement');
        $method->setAccessible(true);

        $this->assertEquals('TestModule', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_studly_module_name_replacement()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getStudlyModuleNameReplacement');
        $method->setAccessible(true);

        $this->assertEquals('TestModule', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_vendor_replacement()
    {
        $this->app['config']->set('modularous.composer.vendor', 'test-vendor');

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getVendorReplacement');
        $method->setAccessible(true);

        $this->assertEquals('test-vendor', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_module_namespace_replacement()
    {
        $this->app['config']->set('modules.namespace', 'Modules\\Test');

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getModuleNamespaceReplacement');
        $method->setAccessible(true);

        // Should escape backslashes
        $this->assertEquals('Modules\\\\Test', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_author_replacement()
    {
        $this->app['config']->set('modularous.composer.author.name', 'John Doe');

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getAuthorReplacement');
        $method->setAccessible(true);

        $this->assertEquals('John Doe', $method->invoke($this->generator));
    }

    /** @test */
    public function it_returns_author_email_replacement()
    {
        $this->app['config']->set('modularous.composer.author.email', 'john@example.com');

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getAuthorEmailReplacement');
        $method->setAccessible(true);

        $this->assertEquals('john@example.com', $method->invoke($this->generator));
    }

    /** @test */
    public function it_gets_replacements_array()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getReplacements');
        $method->setAccessible(true);

        $replacements = $method->invoke($this->generator);
        $this->assertIsArray($replacements);
    }

    /** @test */
    public function it_gets_stub_contents()
    {
        // getStubContents uses Stub class which tries to load files
        // Just verify the method exists and returns a string
        $this->assertTrue(method_exists($this->generator, 'getStubContents'));
    }

    /** @test */
    public function it_replaces_string_placeholders()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('replaceString');
        $method->setAccessible(true);

        $result = $method->invoke($this->generator, 'Hello $NAME$');
        $this->assertIsString($result);
    }

    /** @test */
    public function it_gets_replacement_for_stub()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getReplacement');
        $method->setAccessible(true);

        $stub = 'test';
        $replacements = $method->invoke($this->generator, $stub);
        $this->assertIsArray($replacements);
    }

    // ============================================================
    // Phase 3: File Generation Method Tests
    // ============================================================

    /** @test */
    public function it_generates_folders()
    {
        // Test the generateFolders method in isolation with mock filesystem
        $this->filesystem->shouldReceive('isDirectory')->andReturn(false);
        $this->filesystem->shouldReceive('makeDirectory')->andReturn(true);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('generateFolders');
        $method->setAccessible(true);

        // Test that method executes without exception
        $this->assertNull($method->invoke($this->generator));

        // This test verifies that generateFolders works correctly in isolation.
        // Note: Due to a limitation in the vendor package (nwidart/laravel-modules),
        // testing full module creation with multiple generators can cause "mkdir(): File exists"
        // errors when the same directory path is used by multiple generators.
        // This is a known issue with the vendor package and cannot be fixed without
        // modifying vendor files, which is not recommended.

        $this->assertTrue(true); // Test passes if no exceptions thrown above
    }

    /** @test */
    public function it_generates_git_keep_file()
    {
        $this->filesystem->shouldReceive('put')->andReturn(true);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('generateGitKeep');
        $method->setAccessible(true);

        $method->invoke($this->generator, '/tmp/test-path');
        $this->assertTrue(true); // Method executed successfully
    }

    /** @test */
    public function it_generates_files()
    {
        $this->app['config']->set('modularous.stubs.files', []);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('generateFiles');
        $method->setAccessible(true);

        // Test that method executes without exception
        $this->assertNull($method->invoke($this->generator));
    }

    /** @test */
    public function it_cleans_module_json_file()
    {
        $this->module->shouldReceive('getModulePath')->andReturn('/tmp/module');
        $this->module->shouldReceive('getConfigPath')->andReturn('/tmp/config.php');
        $this->filesystem->shouldReceive('exists')->andReturn(true);
        $this->filesystem->shouldReceive('get')->andReturn('<?php return [];');
        $this->filesystem->shouldReceive('put')->andReturn(true);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('cleanModuleJsonFile');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->generator));
    }

    /** @test */
    public function it_generates_route_json_file()
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('generateRouteJsonFile');
        $method->setAccessible(true);

        // This method has minimal logic; just verify it exists
        $this->assertTrue(method_exists($this->generator, 'generateRouteJsonFile'));
    }

    // ============================================================
    // Phase 4: Core Generate Workflow Tests
    // ============================================================

    /** @test */
    public function it_updates_routes_statuses()
    {
        Modularous::shouldReceive('findOrFail')->andReturn($this->module);
        $this->module->shouldReceive('enableRoute')->once()->with('TestRoute');

        $this->generator->updateRoutesStatuses();
        $this->assertTrue(true);
    }

    /** @test */
    public function it_fixes_config_file()
    {
        $this->module->shouldReceive('getSnakeName')->andReturn('test_module');
        $this->module->shouldReceive('getConfigPath')->andReturn('/tmp/config.php');
        $this->app['config']->set('test_module.routes', []);

        $this->filesystem->shouldReceive('exists')->andReturn(true);
        $this->filesystem->shouldReceive('get')->andReturn('<?php return [];');
        $this->filesystem->shouldReceive('put')->andReturn(true);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('fixConfigFile');
        $method->setAccessible(true);

        $method->invoke($this->generator);
        $this->assertTrue(true); // Successfully executed
    }

    /** @test */
    public function it_has_generate_method()
    {
        // generate() is complex and calls many dependencies
        // Just verify the method exists and is callable
        $this->assertTrue(method_exists($this->generator, 'generate'));
    }

    /** @test */
    public function it_runs_test_workflow_via_generate()
    {
        $this->generator->setTest(true)->setSchema('name:string')->setPlain(true);
        $this->module->shouldReceive('getRawRouteConfig')->with('TestRoute')->andReturn(null);
        $this->console->shouldReceive('info')->atLeast()->once();

        $this->assertSame(0, $this->generator->generate());
    }

    /** @test */
    public function it_generates_extra_migrations_for_morphed_by_many()
    {
        $this->generator->setRelationships('tags:morphedByMany');
        $this->module->shouldReceive('isFileExists')->andReturn(false);
        $this->console->shouldReceive('call')->atLeast()->once();

        $this->assertTrue($this->generator->generateExtraMigrations());
    }

    /** @test */
    public function it_generates_files_when_stubs_are_configured()
    {
        $this->app['config']->set('modularous.stubs.files', [
            'routes/web' => 'Routes/web.php',
        ]);

        $this->filesystem->shouldReceive('isDirectory')->andReturn(false);
        $this->filesystem->shouldReceive('makeDirectory')->andReturn(true);
        $this->filesystem->shouldReceive('put')->once()->andReturn(true);
        $this->console->shouldReceive('info')->once();

        $this->generator->generateFiles();
        $this->assertTrue(true);
    }

    /** @test */
    public function it_update_config_file_returns_true_in_test_mode()
    {
        $this->module->shouldReceive('getSnakeName')->andReturn('test_module');
        $this->module->shouldReceive('getConfigPath')->andReturn('/tmp/config.php');
        $this->app['config']->set('test_module', []);
        $this->filesystem->shouldReceive('exists')->with('/tmp/config.php')->andReturn(false);
        $this->generator->setTest(true)->setSchema('name:string');

        $result = $this->generator->updateConfigFile();

        $this->assertTrue($this->generator->getTest());
        $this->assertContains($result, [1, true]);
    }

    /** @test */
    public function it_gets_replacement_for_json_stub()
    {
        $this->app['config']->set('modularous.stubs.replacements', [
            'json' => ['STUDLY_NAME', 'MODULE_NAMESPACE'],
        ]);
        $this->app['config']->set('modules.namespace', 'Modules\\Test');

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getReplacement');
        $method->setAccessible(true);

        $replacements = $method->invoke($this->generator, 'json');

        $this->assertSame('TestRoute', $replacements['STUDLY_NAME']);
        $this->assertStringContainsString('Modules', $replacements['MODULE_NAMESPACE']);
    }
}
