<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ModuleRouteInspect;

use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectEntry;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectFinding;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectReport;
use Unusualify\Modularous\Tests\TestCase;

class ModuleRouteInspectReportTest extends TestCase
{
    /** @test */
    public function it_filters_findings_only_and_by_feature(): void
    {
        $withFinding = new ModuleRouteInspectEntry(
            module: 'Blog',
            route: 'Post',
            enabled: true,
            parent: false,
            model: 'App\\Post',
            repository: 'App\\PostRepository',
            features: [
                'revisions' => ['present' => true, 'model' => true, 'repository' => true],
                'cmr' => ['present' => false, 'model' => false, 'repository' => false],
            ],
            findings: [
                new ModuleRouteInspectFinding('warning', 'orphan_status', 'orphan'),
            ],
        );

        $cleanCmr = new ModuleRouteInspectEntry(
            module: 'Blog',
            route: 'Page',
            enabled: true,
            parent: true,
            model: 'App\\Page',
            repository: 'App\\PageRepository',
            features: [
                'revisions' => ['present' => false, 'model' => false, 'repository' => false],
                'cmr' => ['present' => true, 'model' => true, 'repository' => true],
            ],
            findings: [],
        );

        $report = new ModuleRouteInspectReport([$withFinding, $cleanCmr]);

        $findingsOnly = $report->filter(findingsOnly: true);
        $this->assertCount(1, $findingsOnly->entries);
        $this->assertSame('Post', $findingsOnly->entries[0]->route);

        $cmrOnly = $report->filter(featureKeys: ['cmr']);
        $this->assertCount(1, $cmrOnly->entries);
        $this->assertSame('Page', $cmrOnly->entries[0]->route);

        $this->assertSame(1, $report->findingCount());
        $this->assertArrayHasKey('findings', $report->toArray()[0]);
    }
}
