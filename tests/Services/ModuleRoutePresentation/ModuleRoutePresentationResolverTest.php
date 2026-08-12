<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ModuleRoutePresentation;

use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\ModuleRoute;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;
use Unusualify\Modularous\Services\ModuleRouteInspect\FeatureDetector;
use Unusualify\Modularous\Services\ModuleRoutePresentation\ModuleRoutePresentationResolver;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestModulesCase;

class ModuleRoutePresentationResolverTest extends TestModulesCase
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
    public function it_defaults_to_config_driver_and_reads_inline_arrays(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $route = $module->route('Item');
        $this->assertNotNull($route);

        $resolver = app(ModuleRoutePresentationResolver::class);

        $this->assertSame('config', $resolver->driverFor($route, 'inputs'));
        $this->assertSame('config', $route->presentationDriver('inputs'));

        $inputs = $route->inputs();
        $headers = $route->headers();

        $this->assertNotEmpty($inputs);
        $this->assertNotEmpty($headers);
        $this->assertSame($inputs, $resolver->fromConfig($route, 'inputs'));
    }

    /** @test */
    public function it_builds_blueprint_convention_class_names(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $route = $module->route('Item');
        $this->assertNotNull($route);

        $resolver = app(ModuleRoutePresentationResolver::class);

        $this->assertSame(
            'TestModules\\TestModule\\Blueprint\\Item\\Form\\ItemFormInputs',
            $resolver->conventionClass($route, 'inputs')
        );
        $this->assertSame(
            'TestModules\\TestModule\\Blueprint\\Item\\Index\\ItemIndexColumns',
            $resolver->conventionClass($route, 'headers')
        );
        $this->assertSame(
            'TestModules\\TestModule\\Blueprint\\Item\\Index\\ItemIndexOptions',
            $resolver->conventionClass($route, 'table_options')
        );
    }

    /** @test */
    public function it_resolves_class_driver_from_explicit_class(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');

        $formDir = $module->getDirectoryPath('Blueprint/Item/Form');
        $indexDir = $module->getDirectoryPath('Blueprint/Item/Index');
        foreach ([$formDir, $indexDir] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $inputsFqcn = 'TestModules\\TestModule\\Blueprint\\Item\\Form\\ItemFormInputs';
        $headersFqcn = 'TestModules\\TestModule\\Blueprint\\Item\\Index\\ItemIndexColumns';

        file_put_contents($formDir . '/ItemFormInputs.php', <<<'PHP'
<?php
namespace TestModules\TestModule\Blueprint\Item\Form;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;
final class ItemFormInputs implements ModuleRouteInputsProvider {
    public function __invoke(ModuleRoute $route): array {
        return [['name' => 'from_class', 'type' => 'text', 'label' => 'From class']];
    }
}
PHP);

        file_put_contents($indexDir . '/ItemIndexColumns.php', <<<'PHP'
<?php
namespace TestModules\TestModule\Blueprint\Item\Index;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;
final class ItemIndexColumns implements ModuleRouteHeadersProvider {
    public function __invoke(ModuleRoute $route): array {
        return [['title' => 'From class', 'key' => 'from_class']];
    }
}
PHP);

        require_once $formDir . '/ItemFormInputs.php';
        require_once $indexDir . '/ItemIndexColumns.php';

        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            'blueprint' => [
                'inputs' => ['driver' => 'class', 'class' => $inputsFqcn],
                'headers' => ['driver' => 'class', 'class' => $headersFqcn],
            ],
        ]), 'routes.item');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame('class', $route->presentationDriver('inputs'));
        $this->assertSame('from_class', $route->inputs()[0]['name'] ?? null);
        $this->assertSame('from_class', $route->headers()[0]['key'] ?? null);
        $this->assertSame($inputsFqcn, app(ModuleRoutePresentationResolver::class)->resolveClass($route, 'inputs'));
        $this->assertSame($headersFqcn, app(ModuleRoutePresentationResolver::class)->resolveClass($route, 'headers'));
    }

    /** @test */
    public function presentation_alias_still_resolves_driver_meta(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            'presentation' => ['driver' => 'database'],
        ]), 'routes.item');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame('database', $route->presentationDriver('inputs'));
    }

    /** @test */
    public function database_driver_falls_back_to_config(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            'blueprint' => ['driver' => 'database'],
        ]), 'routes.item');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame('database', $route->presentationDriver('inputs'));
        $this->assertSame(
            app(ModuleRoutePresentationResolver::class)->fromConfig($route, 'inputs'),
            $route->inputs()
        );
    }

    /** @test */
    public function it_maps_legacy_fields_to_nested_config_keys(): void
    {
        $this->assertSame('form.inputs', ModuleRoutePresentationResolver::nestedConfigKey('inputs'));
        $this->assertSame('index.columns', ModuleRoutePresentationResolver::nestedConfigKey('headers'));
        $this->assertSame('index.options', ModuleRoutePresentationResolver::nestedConfigKey('table_options'));
        $this->assertSame('index.filters', ModuleRoutePresentationResolver::nestedConfigKey('table_filters'));
        $this->assertSame('index.advanced_filters', ModuleRoutePresentationResolver::nestedConfigKey('filters'));
        $this->assertSame('index.actions', ModuleRoutePresentationResolver::nestedConfigKey('table_actions'));
        $this->assertSame('index.row_actions', ModuleRoutePresentationResolver::nestedConfigKey('table_row_actions'));
        $this->assertSame('form.actions', ModuleRoutePresentationResolver::nestedConfigKey('form_actions'));
    }

    /** @test */
    public function it_prefers_nested_index_form_arrays_over_legacy_flat_keys(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            'headers' => [['title' => 'Legacy', 'key' => 'legacy']],
            'inputs' => [['name' => 'legacy_input', 'type' => 'text']],
            'table_options' => ['itemsPerPage' => 10],
            'index' => [
                'columns' => [['title' => 'Nested', 'key' => 'nested']],
                'options' => ['itemsPerPage' => 25],
            ],
            'form' => [
                'inputs' => [['name' => 'nested_input', 'type' => 'text']],
            ],
        ]), 'routes.item');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame('nested', $route->headers()[0]['key'] ?? null);
        $this->assertSame('nested_input', $route->inputs()[0]['name'] ?? null);
        $this->assertSame(25, $route->tableOptions()['itemsPerPage'] ?? null);
    }

    /** @test */
    public function it_falls_back_to_legacy_flat_keys_when_nested_absent(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            'table_filters' => ['status' => ['active']],
            'table_actions' => [['name' => 'create']],
            'form_actions' => [['name' => 'save']],
        ]), 'routes.item');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame(['active'], $route->tableFilters()['status'] ?? null);
        $this->assertSame('create', $route->tableActions()[0]['name'] ?? null);
        $this->assertSame('save', $route->formActions()[0]['name'] ?? null);
    }

    /** @test */
    public function read_config_payload_from_array_is_nested_first(): void
    {
        $payload = ModuleRoutePresentationResolver::readConfigPayloadFromArray([
            'table_row_actions' => [['name' => 'legacy']],
            'index' => [
                'row_actions' => [['name' => 'nested']],
                'advanced_filters' => [
                    'columns' => [['slug' => 'locale', 'type' => 'select']],
                ],
            ],
            'filters' => [
                'fixed' => ['company_id' => 1],
                'columns' => [['slug' => 'ignored']],
            ],
        ], 'table_row_actions');

        $this->assertSame('nested', $payload[0]['name'] ?? null);

        $filters = ModuleRoutePresentationResolver::readConfigPayloadFromArray([
            'index' => [
                'advanced_filters' => [
                    'columns' => [['slug' => 'locale']],
                ],
            ],
            'filters' => [
                'fixed' => ['company_id' => 1],
            ],
        ], 'filters');

        $this->assertSame('locale', $filters['columns'][0]['slug'] ?? null);
        $this->assertArrayNotHasKey('fixed', $filters);
    }

    /** @test */
    public function looks_like_provider_meta_detects_class_and_driver(): void
    {
        $this->assertTrue(ModuleRoutePresentationResolver::looksLikeProviderMeta(\stdClass::class));
        $this->assertTrue(ModuleRoutePresentationResolver::looksLikeProviderMeta(['driver' => 'class']));
        $this->assertTrue(ModuleRoutePresentationResolver::looksLikeProviderMeta((object) ['class' => \stdClass::class]));
        $this->assertFalse(ModuleRoutePresentationResolver::looksLikeProviderMeta([['name' => 'edit']]));
    }

    /** @test */
    public function nested_class_string_selects_class_driver(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');

        $indexDir = $module->getDirectoryPath('Blueprint/Item/Index');
        if (! is_dir($indexDir)) {
            mkdir($indexDir, 0755, true);
        }

        $columnsFqcn = 'TestModules\\TestModule\\Blueprint\\Item\\Index\\ItemIndexColumnsNested';
        file_put_contents($indexDir . '/ItemIndexColumnsNested.php', <<<'PHP'
<?php
namespace TestModules\TestModule\Blueprint\Item\Index;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;
final class ItemIndexColumnsNested implements ModuleRouteHeadersProvider {
    public function __invoke(ModuleRoute $route): array {
        return [['title' => 'Nested class', 'key' => 'nested_class']];
    }
}
PHP);
        require_once $indexDir . '/ItemIndexColumnsNested.php';

        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            'headers' => [['title' => 'Legacy', 'key' => 'legacy']],
            'index' => [
                'columns' => $columnsFqcn,
            ],
        ]), 'routes.item');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame('class', $route->presentationDriver('headers'));
        $this->assertSame('nested_class', $route->headers()[0]['key'] ?? null);
        $this->assertSame(
            $columnsFqcn,
            app(ModuleRoutePresentationResolver::class)->resolveClass($route, 'headers')
        );
    }

    /** @test */
    public function explicit_empty_nested_array_wins_over_legacy(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');
        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            'headers' => [['title' => 'Legacy', 'key' => 'legacy']],
            'index' => [
                'columns' => [],
            ],
        ]), 'routes.item');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame([], $route->headers());
    }

    /** @test */
    public function mis_nested_blueprint_form_inputs_class_still_resolves(): void
    {
        /** @var Module $module */
        $module = Modularous::findOrFail('TestModule');

        $formDir = $module->getDirectoryPath('Blueprint/Item/Form');
        if (! is_dir($formDir)) {
            mkdir($formDir, 0755, true);
        }

        $inputsFqcn = 'TestModules\\TestModule\\Blueprint\\Item\\Form\\ItemFormInputsMisNested';
        file_put_contents($formDir . '/ItemFormInputsMisNested.php', <<<'PHP'
<?php
namespace TestModules\TestModule\Blueprint\Item\Form;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;
final class ItemFormInputsMisNested implements ModuleRouteInputsProvider {
    public function __invoke(ModuleRoute $route): array {
        return [['name' => 'from_blueprint_form', 'type' => 'text', 'label' => 'Compat']];
    }
}
PHP);
        require_once $formDir . '/ItemFormInputsMisNested.php';

        $existing = $module->getRouteConfig('Item');
        $module->setConfig(array_merge(is_array($existing) ? $existing : [], [
            // Intentionally wrong ADR shape (compat path): blueprint.form.inputs
            'blueprint' => [
                'form' => [
                    'inputs' => $inputsFqcn,
                ],
            ],
        ]), 'routes.item');
        // Ensure flat / canonical leaves are absent for this case.
        $module->setConfig(null, 'routes.item.inputs');
        $module->setConfig(null, 'routes.item.form');

        $route = new ModuleRoute(
            $module,
            'Item',
            app(ModuleRouteStatusStoreInterface::class),
            app(FeatureDetector::class),
        );

        $this->assertSame('class', $route->presentationDriver('inputs'));
        $this->assertSame('from_blueprint_form', $route->inputs()[0]['name'] ?? null);
        $this->assertSame(
            $inputsFqcn,
            app(ModuleRoutePresentationResolver::class)->resolveClass($route, 'inputs')
        );
    }
}
