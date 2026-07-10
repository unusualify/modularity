<?php

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Services\CacheRelationshipGraph;
use Unusualify\Modularous\Tests\TestModulesCase;

class CacheRelationshipGraphTest extends TestModulesCase
{
    protected CacheRelationshipGraph $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable the graph feature
        Config::set('modularous.cache.graph.enabled', true);
        Config::set('modularous.cache.graph.ttl', 3600);

        // Clear any cached graph
        Cache::forget('modularous:cache:relationship_graph');

        $this->service = new CacheRelationshipGraph;
    }

    protected function tearDown(): void
    {
        Cache::forget('modularous:cache:relationship_graph');

        parent::tearDown();
    }

    /** @test */
    public function it_can_check_if_enabled()
    {
        $this->assertTrue($this->service->isEnabled());

        Config::set('modularous.cache.graph.enabled', false);
        $disabledService = new CacheRelationshipGraph;
        $this->assertFalse($disabledService->isEnabled());
    }

    /** @test */
    public function it_returns_empty_array_when_disabled()
    {
        Config::set('modularous.cache.graph.enabled', false);
        $service = new CacheRelationshipGraph;

        $this->assertEquals([], $service->getAffectedModuleRoutes('App\\Models\\User'));
        $this->assertEquals([], $service->getAffectedModuleRoutesByTable('users'));
    }

    /** @test */
    public function it_can_build_and_cache_graph()
    {
        // The graph should not be cached initially
        $this->assertFalse($this->service->isCached());

        // Building the graph should cache it
        $graph = $this->service->getGraph();

        $this->assertIsArray($graph);
        $this->assertArrayHasKey('model_to_module_routes', $graph);
        $this->assertArrayHasKey('table_to_module_routes', $graph);
        $this->assertArrayHasKey('module_relationships', $graph);
        $this->assertArrayHasKey('submodule_to_module', $graph);

        // Graph should now be cached
        $this->assertTrue($this->service->isCached());
    }

    /** @test */
    public function it_can_rebuild_graph()
    {
        // Build initial graph
        $graph1 = $this->service->getGraph();
        $this->assertTrue($this->service->isCached());

        // Rebuild graph
        $graph2 = $this->service->rebuildGraph();

        $this->assertIsArray($graph2);
        $this->assertTrue($this->service->isCached());
        $this->assertArrayHasKey('model_to_module_routes', $graph2);
    }

    /** @test */
    public function it_can_clear_graph()
    {
        // Build graph
        $this->service->getGraph();
        $this->assertTrue($this->service->isCached());

        // Clear graph
        $this->service->clearGraph();
        $this->assertFalse($this->service->isCached());
    }

    /** @test */
    public function it_returns_empty_graph_structure_when_disabled()
    {
        Config::set('modularous.cache.graph.enabled', false);
        $service = new CacheRelationshipGraph;

        $graph = $service->getGraph();

        $this->assertIsArray($graph);
        $this->assertEquals([], $graph['model_to_module_routes']);
        $this->assertEquals([], $graph['table_to_module_routes']);
        $this->assertEquals([], $graph['module_relationships']);
        $this->assertEquals([], $graph['submodule_to_module']);
    }

    /** @test */
    public function it_can_get_affected_module_routes_by_model()
    {
        // Build graph first
        $this->service->buildGraph();

        // Test with a model class
        $affected = $this->service->getAffectedModuleRoutes('SomeModelClass');

        $this->assertIsArray($affected);
        // Affected should be an array of [moduleName, moduleRouteName] arrays
    }

    /** @test */
    public function it_can_get_affected_module_routes_by_table()
    {
        // Build graph first
        $this->service->buildGraph();

        // Test with a table name
        $affected = $this->service->getAffectedModuleRoutesByTable('some_table');

        $this->assertIsArray($affected);
    }

    /** @test */
    public function it_can_get_stats()
    {
        $stats = $this->service->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('enabled', $stats);
        $this->assertArrayHasKey('cached', $stats);
        $this->assertArrayHasKey('ttl', $stats);
        $this->assertArrayHasKey('total_models_tracked', $stats);
        $this->assertArrayHasKey('total_tables_tracked', $stats);
        $this->assertArrayHasKey('total_module_routes', $stats);

        $this->assertTrue($stats['enabled']);
        $this->assertEquals(3600, $stats['ttl']);
        $this->assertIsInt($stats['total_models_tracked']);
        $this->assertIsInt($stats['total_tables_tracked']);
    }

    /** @test */
    public function it_can_analyze_impact_for_model()
    {
        // Build graph
        $this->service->buildGraph();

        // Analyze impact
        $analysis = $this->service->analyzeImpact('SomeModel');

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('input', $analysis);
        $this->assertArrayHasKey('type', $analysis);
        $this->assertArrayHasKey('affected_module_routes', $analysis);

        $this->assertEquals('SomeModel', $analysis['input']);
        $this->assertIsArray($analysis['affected_module_routes']);
    }

    /** @test */
    public function it_can_analyze_impact_for_table()
    {
        // Build graph
        $this->service->buildGraph();

        // Analyze impact for a table
        $analysis = $this->service->analyzeImpact('some_table');

        $this->assertIsArray($analysis);
        $this->assertEquals('some_table', $analysis['input']);
        $this->assertIsArray($analysis['affected_module_routes']);
    }

    /** @test */
    public function it_can_get_visual_graph()
    {
        $visual = $this->service->getVisualGraph();

        $this->assertIsArray($visual);

        // The visual graph should group by module
        // Each module should have submodules with relationships
    }

    /** @test */
    public function it_uses_memory_cache_on_subsequent_calls()
    {
        // First call builds and caches
        $graph1 = $this->service->getGraph();

        // Second call should use in-memory graph (not rebuild)
        $graph2 = $this->service->getGraph();

        // Both should be the same instance since it's cached in memory
        $this->assertSame($graph1, $graph2);
    }

    /** @test */
    public function it_handles_modules_without_relationships_gracefully()
    {
        // This should not throw errors even if modules don't have relationships
        $graph = $this->service->buildGraph();

        $this->assertIsArray($graph);
        $this->assertArrayHasKey('model_to_module_routes', $graph);
    }

    /** @test */
    public function it_returns_empty_affected_routes_for_unknown_model()
    {
        $this->service->buildGraph();

        $affected = $this->service->getAffectedModuleRoutes('NonExistent\\Model\\Class');

        $this->assertIsArray($affected);
        $this->assertEmpty($affected);
    }

    /** @test */
    public function it_returns_empty_affected_routes_for_unknown_table()
    {
        $this->service->buildGraph();

        $affected = $this->service->getAffectedModuleRoutesByTable('non_existent_table');

        $this->assertIsArray($affected);
        $this->assertEmpty($affected);
    }

    /** @test */
    public function analyze_impact_returns_null_type_for_unknown_input()
    {
        $this->service->buildGraph();

        $analysis = $this->service->analyzeImpact('UnknownModelOrTable');

        $this->assertIsArray($analysis);
        $this->assertNull($analysis['type']);
        $this->assertEmpty($analysis['affected_module_routes']);
    }

    /** @test */
    public function it_caches_graph_with_configured_ttl()
    {
        Config::set('modularous.cache.graph.ttl', 7200);
        $service = new CacheRelationshipGraph;

        $stats = $service->getStats();

        $this->assertEquals(7200, $stats['ttl']);
    }

    /** @test */
    public function rebuild_graph_clears_memory_and_cache()
    {
        // Build initial graph
        $graph1 = $this->service->getGraph();
        $this->assertTrue($this->service->isCached());

        // Clear cache manually to simulate cache expiration
        Cache::forget('modularous:cache:relationship_graph');

        // Rebuild should detect missing cache and rebuild
        $graph2 = $this->service->rebuildGraph();

        $this->assertIsArray($graph2);
        $this->assertTrue($this->service->isCached());
    }

    /** @test */
    public function it_maps_middleman_relationships_into_model_and_table_indexes(): void
    {
        $graph = [
            'model_to_module_routes' => [],
            'table_to_module_routes' => [],
            'module_relationships' => [],
            'submodule_to_module' => [],
        ];

        $method = new \ReflectionMethod($this->service, 'processRelationship');
        $method->setAccessible(true);
        $method->invokeArgs(
            $this->service,
            [
                'Blog',
                'Post',
                'tags',
                [
                    'relationship_model' => 'Modules\\Blog\\Entities\\Tag',
                    'relationship_table' => 'tags',
                    'relationship_class' => 'BelongsToMany',
                    'has_middleman' => true,
                    'middleman_model' => 'Modules\\Blog\\Entities\\PostTag',
                    'middleman_table' => 'post_tag',
                ],
                &$graph,
            ]
        );

        $this->assertSame(
            [['moduleName' => 'Blog', 'moduleRouteName' => 'Post']],
            $graph['model_to_module_routes']['Modules\\Blog\\Entities\\Tag']
        );
        $this->assertSame(
            [['moduleName' => 'Blog', 'moduleRouteName' => 'Post']],
            $graph['table_to_module_routes']['tags']
        );
        $this->assertSame(
            [['moduleName' => 'Blog', 'moduleRouteName' => 'Post']],
            $graph['model_to_module_routes']['Modules\\Blog\\Entities\\PostTag']
        );
        $this->assertSame(
            [['moduleName' => 'Blog', 'moduleRouteName' => 'Post']],
            $graph['table_to_module_routes']['post_tag']
        );
    }

    /** @test */
    public function it_builds_visual_graph_with_affected_by_metadata(): void
    {
        $graph = [
            'model_to_module_routes' => [
                'Modules\\Blog\\Entities\\Author' => [
                    ['moduleName' => 'Blog', 'moduleRouteName' => 'Post'],
                ],
            ],
            'table_to_module_routes' => [],
            'module_relationships' => [
                'Blog' => [
                    'Post' => [
                        'model_class' => 'Modules\\Blog\\Entities\\Post',
                        'parent_module' => 'Blog',
                        'relationships' => [
                            'author' => [
                                'model' => 'Modules\\Blog\\Entities\\Author',
                                'type' => 'BelongsTo',
                            ],
                        ],
                    ],
                ],
            ],
            'submodule_to_module' => [],
        ];

        $property = new \ReflectionProperty($this->service, 'graph');
        $property->setAccessible(true);
        $property->setValue($this->service, $graph);

        $visual = $this->service->getVisualGraph();

        $this->assertArrayHasKey('Blog', $visual);
        $this->assertSame('Modules\\Blog\\Entities\\Post', $visual['Blog']['module_routes']['Post']['model_class']);
        $this->assertContains('Author', $visual['Blog']['module_routes']['Post']['affected_by']);
    }

    /** @test */
    public function it_scans_entity_directories_for_concrete_models(): void
    {
        $directory = __DIR__ . '/../Stubs/Modules/SourceModule/Entities';

        $method = new \ReflectionMethod($this->service, 'scanDirectoryForModels');
        $method->setAccessible(true);
        $models = $method->invoke($this->service, $directory, 'Modules\\SourceModule\\Entities');

        $this->assertContains('Modules\\SourceModule\\Entities\\RelationshipSourceModel', $models);
    }
}
