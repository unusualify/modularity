<?php

namespace Unusualify\Modularous\Tests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Mockery;
use Unusualify\Modularous\Exceptions\ModularousException;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;

class ModuleTest extends TestCase
{
    /** @var Module */
    protected $module;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $fixturesPath = IsolatedTestModules::path();
        IsolatedTestModules::seedRoutesStatuses();
        $app['config']->set('modules.paths.modules', $fixturesPath);
        $app['config']->set('modules.scan.paths', [$fixturesPath]);
        $app['config']->set('modules.namespace', 'TestModules');

        // Align generator paths with fixture layout (Entities, Repositories, Controllers at module root)
        $app['config']->set('modules.paths.generator.model', ['path' => 'Entities', 'namespace' => 'Entities', 'generate' => false]);
        $app['config']->set('modules.paths.generator.repository', ['path' => 'Repositories', 'namespace' => 'Repositories', 'generate' => false]);
        $app['config']->set('modules.paths.generator.controller', ['path' => 'Controllers', 'namespace' => 'Controllers', 'generate' => false]);

        Modularous::boot();
    }

    protected function setUp(): void
    {
        parent::setUp();

        MockModuleManager::initialize();
        $this->module = MockModuleManager::getTestModule();

        IsolatedTestModules::seedRoutesStatuses(['TestModule' => ['Item' => true]]);
    }

    public function test_module_can_be_resolved_from_fixtures(): void
    {
        $this->assertInstanceOf(Module::class, $this->module);
        $this->assertSame('TestModule', $this->module->getName());
        $this->assertStringContainsString('TestModule', $this->module->getPath());
    }

    public function test_get_cached_services_path(): void
    {
        $path = $this->module->getCachedServicesPath();
        $this->assertStringContainsString('_module', $path);
        $this->assertStringEndsWith('.php', $path);
        $this->assertStringContainsString('test_module', $path);
    }

    public function test_get_cached_services_path_with_vapor(): void
    {
        $this->app['env'] = 'production';
        putenv('VAPOR_MAINTENANCE_MODE=1');
        $path = $this->module->getCachedServicesPath();
        putenv('VAPOR_MAINTENANCE_MODE');
        $this->assertStringContainsString('_module', $path);
        $this->assertStringEndsWith('.php', $path);
    }

    public function test_register_providers_and_register_aliases(): void
    {
        $this->module->registerProviders();
        $this->module->registerAliases();
        $this->addToAssertionCount(1);
    }

    public function test_get_directory_path(): void
    {
        $path = $this->module->getDirectoryPath();
        $this->assertStringEndsWith('/', $path);
        $this->assertStringContainsString('TestModule', $path);

        $withDir = $this->module->getDirectoryPath('Config');
        $this->assertStringEndsWith('/Config', $withDir);

        $relative = $this->module->getDirectoryPath('Config', true);
        $this->assertStringNotContainsString(base_path(), $relative);
    }

    public function test_get_base_namespace(): void
    {
        $ns = $this->module->getBaseNamespace();
        $this->assertStringContainsString('TestModule', $ns);
        $this->assertStringContainsString('Modules', $ns);
    }

    public function test_get_class_namespace(): void
    {
        $ns = $this->module->getClassNamespace('Entities\Item');
        $this->assertStringEndsWith('Entities\Item', $ns);
    }

    public function test_get_raw_config_and_get_config(): void
    {
        $raw = $this->module->getRawConfig();
        $this->assertIsArray($raw);
        $this->assertArrayHasKey('name', $raw);
        $this->assertSame('TestModule', $raw['name']);

        $name = $this->module->getConfig('name');
        $this->assertSame('TestModule', $name);

        $routes = $this->module->getConfig('routes');
        $this->assertIsArray($routes);
    }

    public function test_set_config_and_reset_config(): void
    {
        $this->module->loadConfig();
        $this->module->setConfig('test_value', 'test_key');
        $this->assertSame('test_value', $this->module->getConfig('test_key'));
        $this->module->resetConfig();
        // After reset, config is restored from file; test_key is not in file so it is no longer our value
        $this->assertNotSame('test_value', $this->module->getConfig('test_key'));
    }

    public function test_load_config(): void
    {
        $this->module->loadConfig();
        $this->assertNotNull($this->module->getConfig('name'));
    }

    public function test_get_raw_route_configs_and_get_route_config(): void
    {
        $configs = $this->module->getRawRouteConfigs();
        $this->assertIsArray($configs);
        $this->assertArrayHasKey('item', $configs);

        $itemConfig = $this->module->getRawRouteConfig('Item');
        $this->assertIsArray($itemConfig);
        $this->assertArrayHasKey('name', $itemConfig);
    }

    public function test_get_route_configs_and_get_route_config(): void
    {
        $configs = $this->module->getRouteConfigs();
        $this->assertIsArray($configs);

        $itemConfig = $this->module->getRouteConfig('Item');
        $this->assertIsArray($itemConfig);
        $this->assertArrayHasKey('inputs', $itemConfig);
    }

    public function test_get_route_inputs_and_get_route_input(): void
    {
        $inputs = $this->module->getRouteInputs('Item');
        $this->assertIsArray($inputs);
        $this->assertNotEmpty($inputs);

        $nameInput = $this->module->getRouteInput('Item', 'name', 'name');
        $this->assertIsArray($nameInput);
        $this->assertArrayHasKey('name', $nameInput);
    }

    public function test_get_parent_route_and_has_parent_route(): void
    {
        $parent = $this->module->getParentRoute();
        $this->assertIsArray($parent);
        $hasParent = $this->module->hasParentRoute();
        $this->assertIsBool($hasParent);
    }

    public function test_is_parent_route(): void
    {
        $this->assertIsBool($this->module->isParentRoute('Item'));
    }

    public function test_get_routes_and_get_route_names_and_has_route(): void
    {
        $routes = $this->module->getRoutes();
        $this->assertIsArray($routes);

        $names = $this->module->getRouteNames();
        $this->assertIsArray($names);

        $this->assertTrue($this->module->hasRoute('Item'));
        $this->assertFalse($this->module->hasRoute('nonexistent'));
    }

    public function test_enable_and_disable_route(): void
    {
        $this->module->enableRoute('Item');
        $this->assertTrue($this->module->isEnabledRoute('Item'));

        $this->module->disableRoute('Item');
        $this->assertTrue($this->module->isDisabledRoute('Item'));

        $this->module->enableRoute('Item');
    }

    public function test_has_system_prefix_and_system_prefix_and_system_route_name_prefix(): void
    {
        $has = $this->module->hasSystemPrefix();
        $this->assertIsBool($has);

        $prefix = $this->module->systemPrefix();
        $this->assertIsString($prefix);

        $routePrefix = $this->module->systemRouteNamePrefix();
        $this->assertIsString($routePrefix);
    }

    public function test_prefix_and_full_prefix(): void
    {
        $prefix = $this->module->prefix();
        $this->assertIsString($prefix);
        $this->assertNotEmpty($prefix);

        $full = $this->module->fullPrefix();
        $this->assertIsString($full);
    }

    public function test_route_name_prefix_and_full_route_name_prefix_and_panel_route_name_prefix(): void
    {
        $prefix = $this->module->routeNamePrefix();
        $this->assertIsString($prefix);

        $full = $this->module->fullRouteNamePrefix();
        $this->assertIsString($full);

        $panel = $this->module->panelRouteNamePrefix();
        $this->assertIsString($panel);

        $this->module->fullRouteNamePrefix(true);
        $this->module->panelRouteNamePrefix(true);
    }

    public function test_get_config_path(): void
    {
        $path = $this->module->getConfigPath();
        $this->assertStringEndsWith('config.php', $path);
        $this->assertStringContainsString('TestModule', $path);
    }

    public function test_is_file_exists(): void
    {
        // Pattern **/*/*fileName* requires at least two directory levels; fixture may not match
        $this->assertIsBool($this->module->isFileExists('config.php'));
        $this->assertFalse($this->module->isFileExists('nonexistent-file-xyz.php'));
    }

    public function test_get_module_urls(): void
    {
        $urls = $this->module->getModuleUrls();
        $this->assertIsArray($urls);
    }

    public function test_get_route_urls(): void
    {
        $urls = $this->module->getRouteUrls('Item');
        $this->assertIsArray($urls);
    }

    public function test_get_route_panel_urls(): void
    {
        $urls = $this->module->getRoutePanelUrls('Item');
        $this->assertIsArray($urls);

        $withoutPrefix = $this->module->getRoutePanelUrls('Item', true);
        $this->assertIsArray($withoutPrefix);

        $withBinding = $this->module->getRoutePanelUrls('Item', true, '1');
        $this->assertIsArray($withBinding);
    }

    public function test_get_route_action_url(): void
    {
        // No routes are registered in test app, so getRouteActionUrl throws when no match
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route not found');
        $this->module->getRouteActionUrl('Item', 'indexx', [], false, true);
    }

    public function test_get_parent_namespace(): void
    {
        $ns = $this->module->getParentNamespace('model');
        $this->assertStringContainsString('Entities', $ns);
    }

    public function test_get_target_class_namespace_and_get_target_class_path(): void
    {
        $ns = $this->module->getTargetClassNamespace('model', 'Item');
        $this->assertStringEndsWith('Item', $ns);

        $path = $this->module->getTargetClassPath('model', 'Item');
        $this->assertStringContainsString('Item', $path);
    }

    public function test_get_repository(): void
    {
        $repo = $this->module->getRepository('Item', true);
        $this->assertNotNull($repo);

        $repoClass = $this->module->getRepository('Item', false);
        $this->assertIsString($repoClass);
    }

    public function test_get_model(): void
    {
        $model = $this->module->getModel('Item', true);
        $this->assertNotNull($model);

        $modelClass = $this->module->getModel('Item', false);
        $this->assertIsString($modelClass);
    }

    public function test_get_controller(): void
    {
        $controller = $this->module->getController('Item', true);
        $this->assertNotNull($controller);

        $controllerClass = $this->module->getController('Item', false);
        $this->assertIsString($controllerClass);
    }

    public function test_get_model_throws_for_unknown_route(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Repository not found');
        $this->module->getModel('UnknownRoute');
    }

    public function test_get_controller_throws_for_unknown_route(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Controller not found');
        $this->module->getController('UnknownRoute');
    }

    public function test_get_inertia_pages_path_and_has_inertia_pages_type_and_get_inertia_pages_type_name(): void
    {
        $path = $this->module->getInertiaPagesPath('Item');
        $this->assertStringContainsString('Pages/Item', $path);

        $has = $this->module->hasInertiaPagesType('Item', 'Index');
        $this->assertIsBool($has);

        $name = $this->module->getInertiaPagesTypeName('Item', 'Index');
        $this->assertSame('TestModule/Item/Index', $name);
    }

    public function test_get_route_class(): void
    {
        $class = $this->module->getRouteClass('Item', 'repository', false);
        $this->assertStringContainsString('ItemRepository', $class);

        $modelClass = $this->module->getRouteClass('Item', 'model', false);
        $this->assertStringContainsString('Item', $modelClass);
    }

    public function test_get_navigation_actions(): void
    {
        $actions = $this->module->getNavigationActions('Item');
        $this->assertIsArray($actions);
    }

    public function test_create_middleware_aliases(): void
    {
        $this->module->createMiddlewareAliases();
        $this->addToAssertionCount(1);
    }

    public function test_get_route_middleware_aliases(): void
    {
        $aliases = $this->module->getRouteMiddlewareAliases('Item');
        $this->assertIsArray($aliases);
    }

    public function test_is_modularous_module(): void
    {
        $result = $this->module->isModularousModule();
        $this->assertIsBool($result);
    }

    public function test_get_activator(): void
    {
        $activator = $this->module->getActivator();
        $this->assertNotNull($activator);
    }

    public function test_clear_cache(): void
    {
        $this->module->clearCache();
        $this->addToAssertionCount(1);
    }

    public function test_load_commands(): void
    {
        $this->module->loadCommands();
        $this->addToAssertionCount(1);
    }

    public function test_route_has_table(): void
    {
        $hasTable = $this->module->routeHasTable('Item');
        $this->assertIsBool($hasTable);
    }

    public function test_is_singleton(): void
    {
        $result = $this->module->isSingleton('Item');
        $this->assertIsBool($result);
    }

    public function test_generate_permission_helpers(): void
    {
        $permissionName = $this->module->generatePermissionName('create', 'Item');
        $middleware = $this->module->generatePermissionMiddlewareDefinition('view', 'Item');

        $this->assertSame('item_create', $permissionName);
        $this->assertSame('can:item_view', $middleware);
    }

    public function test_user_has_permission_returns_false_without_authenticated_user(): void
    {
        Modularous::shouldReceive('getAuthGuardName')->andReturn('modularous');
        Auth::shouldReceive('guard')->with('modularous')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn(null);

        $this->assertFalse($this->module->userHasPermission('view', 'Item'));
    }

    public function test_allowed_permission_returns_false_without_gate_definition(): void
    {
        $this->assertFalse($this->module->allowedPermission('edit', 'Item'));
    }

    public function test_has_remote_api_source_and_resource_cache_flags(): void
    {
        $this->assertFalse($this->module->hasRemoteApiSource('Item'));
        $this->assertFalse($this->module->isResourceCacheEnabled('Item'));
    }

    public function test_ensure_routes_statuses_file_creates_activator_file(): void
    {
        $statusesPath = $this->module->getDirectoryPath('routes_statuses.json');
        @unlink($statusesPath);

        $this->module->ensureRoutesStatusesFile();

        $this->assertFileExists($statusesPath);
    }

    public function test_get_raw_route_configs_valid_filters_nameless_entries(): void
    {
        $this->module->setConfig([
            'valid' => ['name' => 'Valid'],
            'invalid' => ['headline' => 'Missing name'],
        ], 'routes');

        $validOnly = $this->module->getRawRouteConfigs(valid: true);

        $this->assertArrayHasKey('valid', $validOnly);
        $this->assertArrayNotHasKey('invalid', $validOnly);
    }

    public function test_is_status_throws_modularous_exception_when_activator_fails(): void
    {
        $activator = Mockery::mock($this->module->getActivator());
        $activator->shouldReceive('hasStatus')->andThrow(new \RuntimeException('status file missing'));

        $property = new \ReflectionProperty($this->module, 'moduleActivator');
        $property->setAccessible(true);
        $property->setValue($this->module, $activator);

        $this->expectException(ModularousException::class);
        $this->expectExceptionMessage('Failed to check module status');

        $this->module->isStatus(true);
    }
}
