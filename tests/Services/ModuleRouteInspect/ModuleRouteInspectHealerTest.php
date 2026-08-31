<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ModuleRouteInspect;

use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteInspectSource;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectEntry;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectFinding;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectHealer;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectRemedyMapper;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectReport;
use Unusualify\Modularous\Tests\TestCase;

class ModuleRouteInspectHealerTest extends TestCase
{
    /** @test */
    public function it_suggests_remake_lines_from_report(): void
    {
        $entry = new ModuleRouteInspectEntry(
            module: 'PrimaryPage',
            route: 'AboutUs',
            enabled: true,
            parent: false,
            model: 'App\\AboutUs',
            repository: 'App\\AboutUsRepository',
            features: [
                'cmr' => ['present' => true, 'model' => true, 'repository' => true],
            ],
            findings: [
                new ModuleRouteInspectFinding(
                    'warning',
                    'cmr_missing_front_controller',
                    'missing front',
                    'cmr',
                ),
            ],
        );

        $healer = new ModuleRouteInspectHealer(
            new ModuleRouteInspectRemedyMapper,
            $this->sourceReturning(new ModuleRouteInspectReport([])),
        );

        $suggestions = $healer->suggestFromReport(new ModuleRouteInspectReport([$entry]));

        $this->assertCount(1, $suggestions);
        $this->assertSame('modularous:remake:cmr', $suggestions[0]['remedy']['command']);
        $this->assertStringContainsString('--dry-run', $suggestions[0]['remedy']['artisan']);
    }

    /** @test */
    public function it_runs_allowlisted_remake_via_artisan(): void
    {
        $finding = new ModuleRouteInspectFinding(
            'warning',
            'cmr_missing_front_controller',
            'missing front',
            'cmr',
        );

        $entry = new ModuleRouteInspectEntry(
            module: 'PrimaryPage',
            route: 'AboutUs',
            enabled: true,
            parent: false,
            model: null,
            repository: null,
            features: [],
            findings: [$finding],
        );

        $source = $this->sourceReturning(new ModuleRouteInspectReport([$entry]));

        $called = [];
        $runner = function (string $command, array $parameters) use (&$called): array {
            $called = compact('command', 'parameters');

            return [0, "Dry-run OK\n"];
        };

        $healer = new ModuleRouteInspectHealer(
            new ModuleRouteInspectRemedyMapper,
            $source,
            $runner,
        );

        $result = $healer->healFinding(
            'PrimaryPage',
            'AboutUs',
            'cmr_missing_front_controller',
            'cmr',
            dryRun: true,
        );

        $this->assertTrue($result['ok']);
        $this->assertTrue($result['dry_run']);
        $this->assertSame(0, $result['exit_code']);
        $this->assertStringContainsString('Dry-run OK', $result['output']);
        $this->assertSame('modularous:remake:cmr', $called['command'] ?? null);
        $this->assertSame('PrimaryPage', $called['parameters']['module'] ?? null);
        $this->assertSame('AboutUs', $called['parameters']['route'] ?? null);
        $this->assertTrue($called['parameters']['--dry-run'] ?? false);
    }

    /** @test */
    public function it_rejects_unsafe_remedy_without_force(): void
    {
        $finding = new ModuleRouteInspectFinding(
            'warning',
            'cmr_front_not_cms_controller',
            'not cms',
            'cmr',
        );

        $entry = new ModuleRouteInspectEntry(
            module: 'PrimaryPage',
            route: 'AboutUs',
            enabled: true,
            parent: false,
            model: null,
            repository: null,
            features: [],
            findings: [$finding],
        );

        $healer = new ModuleRouteInspectHealer(
            new ModuleRouteInspectRemedyMapper,
            $this->sourceReturning(new ModuleRouteInspectReport([$entry])),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unsafe');

        $healer->healFinding(
            'PrimaryPage',
            'AboutUs',
            'cmr_front_not_cms_controller',
            'cmr',
            dryRun: true,
            allowUnsafe: false,
        );
    }

    private function sourceReturning(ModuleRouteInspectReport $report): ModuleRouteInspectSource
    {
        return new class($report) implements ModuleRouteInspectSource
        {
            public function __construct(private readonly ModuleRouteInspectReport $report) {}

            public function inspect(?string $moduleName = null, ?string $routeName = null): ModuleRouteInspectReport
            {
                return $this->report;
            }
        };
    }
}
