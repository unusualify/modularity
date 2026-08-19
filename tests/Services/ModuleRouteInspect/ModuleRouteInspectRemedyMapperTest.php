<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ModuleRouteInspect;

use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectFinding;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectRemedy;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectRemedyMapper;
use Unusualify\Modularous\Tests\TestCase;

class ModuleRouteInspectRemedyMapperTest extends TestCase
{
    private ModuleRouteInspectRemedyMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new ModuleRouteInspectRemedyMapper;
    }

    /** @test */
    public function it_maps_cmr_missing_front_to_safe_remake(): void
    {
        $finding = new ModuleRouteInspectFinding(
            'warning',
            'cmr_missing_front_controller',
            'missing front',
            'cmr',
        );

        $remedy = $this->mapper->map('PrimaryPage', 'AboutUs', $finding);

        $this->assertNotNull($remedy);
        $this->assertSame(ModuleRouteInspectRemedy::ACTION_REMAKE, $remedy->action);
        $this->assertSame('modularous:remake:cmr', $remedy->command);
        $this->assertTrue($remedy->safe);
        $this->assertStringContainsString('modularous:remake:cmr', $remedy->artisanLine());
        $this->assertStringContainsString('--dry-run', $remedy->artisanLine());
    }

    /** @test */
    public function it_maps_cmr_front_not_cms_as_unsafe_force_remake(): void
    {
        $finding = new ModuleRouteInspectFinding(
            'warning',
            'cmr_front_not_cms_controller',
            'not cms',
            'cmr',
        );

        $remedy = $this->mapper->map('PrimaryPage', 'AboutUs', $finding);

        $this->assertNotNull($remedy);
        $this->assertFalse($remedy->safe);
        $this->assertTrue($remedy->options['force'] ?? false);
    }

    /** @test */
    public function it_maps_revision_companion_to_remake_revisions(): void
    {
        $finding = new ModuleRouteInspectFinding(
            'warning',
            'missing_repository_companion',
            'Model has HasRevisions but repository is missing companion RevisionsTrait (revisions).',
            'revisions',
        );

        $remedy = $this->mapper->map('Blog', 'Post', $finding);

        $this->assertNotNull($remedy);
        $this->assertSame('modularous:remake:revisions', $remedy->command);
        $this->assertTrue($remedy->safe);
    }

    /** @test */
    public function it_maps_translation_companion_to_manual_tip(): void
    {
        $finding = new ModuleRouteInspectFinding(
            'warning',
            'missing_model_companion',
            'missing model companion (translation).',
            'translation',
        );

        $remedy = $this->mapper->map('Blog', 'Post', $finding);

        $this->assertNotNull($remedy);
        $this->assertSame(ModuleRouteInspectRemedy::ACTION_MANUAL, $remedy->action);
        $this->assertNull($remedy->command);
        $this->assertStringContainsString('translation', (string) $remedy->tip);
    }

    /** @test */
    public function it_parses_feature_from_message_when_missing_on_finding(): void
    {
        $finding = new ModuleRouteInspectFinding(
            'warning',
            'missing_repository_companion',
            'Model has HasRevisions but repository is missing companion RevisionsTrait (revisions).',
        );

        $remedy = $this->mapper->map('Blog', 'Post', $finding);

        $this->assertSame('modularous:remake:revisions', $remedy?->command);
    }
}
