<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Support;

use Unusualify\Modularous\Support\CommandDiscovery;
use Unusualify\Modularous\Tests\TestCase;

class CommandDiscoveryTest extends TestCase
{
    /** @test */
    public function it_discovers_commands_from_given_paths(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/Coverage/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertNotEmpty($commands);
        $this->assertContains('Unusualify\Modularous\Console\Coverage\CoverageWatchCommand', $commands);
        $this->assertContains('Unusualify\Modularous\Console\Coverage\CoverageReportCommand', $commands);
    }

    /** @test */
    public function it_excludes_abstract_classes(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertNotContains('Unusualify\Modularous\Console\BaseCommand', $commands);
    }

    /** @test */
    public function it_handles_missing_directory(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/NonExistentFolder/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertEmpty($commands);
    }

    /** @test */
    public function it_returns_unique_commands_when_paths_overlap(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [
            $basePath . '/Coverage/*.php',
            $basePath . '/Coverage/*.php',
        ];

        $commands = CommandDiscovery::discover($paths);

        $this->assertCount(count(array_unique($commands)), $commands);
    }

    /** @test */
    public function it_discovers_commands_from_make_folder(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/Make/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertNotEmpty($commands);
        $this->assertContains('Unusualify\Modularous\Console\Make\MakeModuleCommand', $commands);
        $this->assertContains('Unusualify\Modularous\Console\Make\MakeRouteCommand', $commands);
        $this->assertContains('Unusualify\Modularous\Console\Make\MakeCmsControllerCommand', $commands);
    }

    /** @test */
    public function it_discovers_commands_from_blueprint_folder(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/Blueprint/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertNotEmpty($commands);
        $this->assertContains('Unusualify\Modularous\Console\Blueprint\MakeBlueprintCommand', $commands);
        $this->assertContains('Unusualify\Modularous\Console\Blueprint\MakeBlueprintFieldCommand', $commands);
    }

    /** @test */
    public function it_discovers_commands_from_remake_folder(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/Remake/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertNotEmpty($commands);
        $this->assertContains('Unusualify\Modularous\Console\Remake\RemakeCmrCommand', $commands);
        $this->assertContains('Unusualify\Modularous\Console\Remake\RemakeRevisionsCommand', $commands);
    }

    /** @test */
    public function it_discovers_commands_from_module_folder(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/Module/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertNotEmpty($commands);
        $this->assertContains('Unusualify\Modularous\Console\Module\RouteInspectCommand', $commands);
        $this->assertContains('Unusualify\Modularous\Console\Module\RouteStatusCommand', $commands);
    }

    /** @test */
    public function it_discovers_root_level_commands(): void
    {
        $basePath = realpath(__DIR__ . '/../../src/Console');
        $paths = [$basePath . '/*.php'];

        $commands = CommandDiscovery::discover($paths);

        $this->assertNotEmpty($commands);
        $this->assertContains('Unusualify\Modularous\Console\PintCommand', $commands);
        $this->assertContains('Unusualify\Modularous\Console\BuildCommand', $commands);
    }
}
