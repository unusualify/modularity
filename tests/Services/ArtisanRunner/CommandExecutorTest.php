<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ArtisanRunner;

use Unusualify\Modularous\Services\ArtisanRunner\CommandExecutor;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;
use Unusualify\Modularous\Tests\TestCase;

class CommandExecutorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'modularous.artisan_runner.execution' => 'auto',
            'modularous.artisan_runner.php_binary' => null,
            'modularous.artisan_runner.subprocess_commands' => [
                'route:cache',
                'route:clear',
                'config:cache',
                'optimize',
                'optimize:clear',
            ],
        ]);
    }

    /** @test */
    public function it_selects_subprocess_for_route_cache_in_auto_mode(): void
    {
        $executor = $this->app->make(CommandExecutor::class);

        $this->assertTrue($executor->shouldExecuteViaSubprocess('route:cache'));
        $this->assertTrue($executor->shouldExecuteViaSubprocess('optimize'));
        $this->assertFalse($executor->shouldExecuteViaSubprocess('inspire'));
        $this->assertFalse($executor->shouldExecuteViaSubprocess('down'));
    }

    /** @test */
    public function it_respects_in_process_execution_mode(): void
    {
        config(['modularous.artisan_runner.execution' => 'in_process']);

        $executor = $this->app->make(CommandExecutor::class);

        $this->assertFalse($executor->shouldExecuteViaSubprocess('route:cache'));
    }

    /** @test */
    public function it_respects_subprocess_execution_mode(): void
    {
        config(['modularous.artisan_runner.execution' => 'subprocess']);

        $executor = $this->app->make(CommandExecutor::class);

        $this->assertTrue($executor->shouldExecuteViaSubprocess('inspire'));
    }

    /** @test */
    public function it_builds_subprocess_argv_with_no_interaction(): void
    {
        $executor = $this->app->make(CommandExecutor::class);

        $line = $executor->buildSubprocessCommandLine([
            'command' => 'route:cache',
        ]);

        $this->assertGreaterThanOrEqual(4, count($line));
        $this->assertSame('route:cache', $line[2]);
        $this->assertContains('--no-interaction', $line);
        $this->assertTrue(is_file($line[1]));
    }

    /** @test */
    public function it_builds_subprocess_argv_with_flags_and_options(): void
    {
        $executor = $this->app->make(CommandExecutor::class);

        $line = $executor->buildSubprocessCommandLine([
            'command' => 'down',
            '--force' => true,
            '--retry' => 60,
            '--secret' => 'abc',
        ]);

        $this->assertSame('down', $line[2]);
        $this->assertContains('--force', $line);
        $this->assertContains('--retry=60', $line);
        $this->assertContains('--secret=abc', $line);
        $this->assertContains('--no-interaction', $line);
    }

    /** @test */
    public function it_resolves_a_php_cli_binary_in_cli_environments(): void
    {
        $executor = $this->app->make(CommandExecutor::class);

        $php = $executor->resolvePhpCliBinary();

        $this->assertNotNull($php);
        $this->assertTrue(is_file($php));
        $this->assertTrue(is_executable($php));
    }

    /** @test */
    public function it_uses_configured_php_binary_for_subprocess_argv(): void
    {
        $executor = $this->app->make(CommandExecutor::class);
        $php = $executor->resolvePhpCliBinary();
        $this->assertNotNull($php);

        config(['modularous.artisan_runner.php_binary' => $php]);
        $executor = $this->app->make(CommandExecutor::class);

        $line = $executor->buildSubprocessCommandLine([
            'command' => 'route:cache',
        ]);

        $this->assertSame($php, $line[0]);
    }

    /** @test */
    public function it_throws_when_configured_php_binary_is_missing(): void
    {
        config(['modularous.artisan_runner.php_binary' => '/definitely/not/a-php-binary']);

        $executor = $this->app->make(CommandExecutor::class);

        $this->expectException(ArtisanRunnerException::class);
        $this->expectExceptionMessage('Unable to use configured PHP CLI binary [/definitely/not/a-php-binary]');

        $executor->buildSubprocessCommandLine([
            'command' => 'route:cache',
        ]);
    }

    /** @test */
    public function it_throws_when_configured_php_binary_is_not_cli(): void
    {
        config(['modularous.artisan_runner.php_binary' => '/usr/sbin/php-fpm8.3']);

        $executor = $this->app->make(CommandExecutor::class);

        $this->expectException(ArtisanRunnerException::class);
        $this->expectExceptionMessage('is not a CLI executable');

        $executor->resolvePhpCliBinary();
    }
}
