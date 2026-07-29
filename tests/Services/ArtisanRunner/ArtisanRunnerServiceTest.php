<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ArtisanRunner;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\CommandNotAllowedException;
use Unusualify\Modularous\Tests\TestCase;

class ArtisanRunnerServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerDemoCommand();

        config([
            'modularous.artisan_runner.enabled' => true,
            'modularous.artisan_runner.allowlist' => ['artisan-runner:demo'],
            'modularous.artisan_runner.allowed_roles' => ['superadmin', 'admin'],
            'modularous.artisan_runner.timeout' => 30,
            'modularous.artisan_runner.max_output_bytes' => 1_048_576,
            'modularous.artisan_runner.prompt_timeout' => 5,
        ]);
    }

    /** @test */
    public function it_denies_access_when_disabled(): void
    {
        config(['modularous.artisan_runner.enabled' => false]);

        $runner = $this->app->make(ArtisanRunnerInterface::class);
        $user = $this->fakeUser(true);

        $this->assertFalse($runner->userCanAccess($user));
        $this->expectException(ArtisanRunnerException::class);
        $runner->catalogForUser($user);
    }

    /** @test */
    public function non_superadmin_cannot_run_commands_outside_allowlist(): void
    {
        $runner = $this->app->make(ArtisanRunnerInterface::class);
        $user = $this->fakeUser(false);

        $this->expectException(CommandNotAllowedException::class);
        $runner->assertCommandAllowed($user, 'artisan-runner:other');
    }

    /** @test */
    public function it_streams_demo_output_for_allowlisted_user(): void
    {
        $runner = $this->app->make(ArtisanRunnerInterface::class);
        $user = $this->fakeUser(false);

        $events = [];
        $exitCode = $runner->run(
            $user,
            'artisan-runner:demo',
            [],
            [],
            'test-run-demo',
            function (string $event, array $payload) use (&$events): void {
                $events[] = ['event' => $event, 'payload' => $payload];
            }
        );

        $this->assertSame(0, $exitCode);
        $eventNames = array_column($events, 'event');
        $this->assertContains('started', $eventNames);
        $this->assertContains('done', $eventNames);
        $this->assertContains('output', $eventNames);

        $chunks = '';
        foreach ($events as $event) {
            if ($event['event'] === 'output') {
                $chunks .= $event['payload']['chunk'] ?? '';
            }
        }
        $this->assertStringContainsString('demo-ok', $chunks);
    }

    /** @test */
    public function it_emits_prompt_and_accepts_confirm_answer_without_stdin(): void
    {
        $this->registerConfirmCommand();

        config([
            'modularous.artisan_runner.allowlist' => [
                'artisan-runner:demo',
                'artisan-runner:confirm',
            ],
        ]);

        $runner = $this->app->make(ArtisanRunnerInterface::class);
        $user = $this->fakeUser(false);
        $runId = 'test-run-confirm';

        $events = [];
        $exitCode = $runner->run(
            $user,
            'artisan-runner:confirm',
            [],
            [],
            $runId,
            function (string $event, array $payload) use (&$events, $runner, $runId): void {
                $events[] = ['event' => $event, 'payload' => $payload];

                if ($event === 'prompt') {
                    $this->assertSame('confirm', $payload['type'] ?? null);
                    $this->assertSame('Delete duplicate media records?', $payload['question'] ?? null);
                    $runner->answerPrompt($runId, (string) $payload['id'], false);
                }
            }
        );

        $this->assertSame(0, $exitCode);

        $eventNames = array_column($events, 'event');
        $this->assertContains('prompt', $eventNames);
        $this->assertNotContains('error', $eventNames);

        $chunks = '';
        foreach ($events as $event) {
            if ($event['event'] === 'output') {
                $chunks .= $event['payload']['chunk'] ?? '';
            }
        }
        $this->assertStringContainsString('aborted', $chunks);
        $this->assertStringNotContainsString('Undefined constant "STDIN"', $chunks);
    }

    /** @test */
    public function it_emits_sequential_prompts_and_completes_multi_step_interaction(): void
    {
        $this->registerMultiPromptCommand();

        config([
            'modularous.artisan_runner.allowlist' => [
                'artisan-runner:demo',
                'artisan-runner:multi-prompt',
            ],
        ]);

        $runner = $this->app->make(ArtisanRunnerInterface::class);
        $user = $this->fakeUser(false);
        $runId = 'test-run-multi-prompt';

        $promptAnswers = [
            'Continue with cleanup?' => true,
            'Target environment?' => 'staging',
        ];
        $promptIds = [];
        $events = [];

        $exitCode = $runner->run(
            $user,
            'artisan-runner:multi-prompt',
            [],
            [],
            $runId,
            function (string $event, array $payload) use (&$events, &$promptIds, $promptAnswers, $runner, $runId): void {
                $events[] = ['event' => $event, 'payload' => $payload];

                if ($event !== 'prompt') {
                    return;
                }

                $question = (string) ($payload['question'] ?? '');
                $this->assertArrayHasKey($question, $promptAnswers);
                $promptIds[] = (string) $payload['id'];
                $runner->answerPrompt($runId, (string) $payload['id'], $promptAnswers[$question]);
            }
        );

        $this->assertSame(0, $exitCode);
        $this->assertCount(2, $promptIds);
        $this->assertNotSame($promptIds[0], $promptIds[1]);

        $eventNames = array_column($events, 'event');
        $this->assertSame(2, count(array_filter($eventNames, static fn (string $name): bool => $name === 'prompt')));
        $this->assertContains('done', $eventNames);
        $this->assertNotContains('error', $eventNames);

        $chunks = '';
        foreach ($events as $event) {
            if ($event['event'] === 'output') {
                $chunks .= $event['payload']['chunk'] ?? '';
            }
        }
        $this->assertStringContainsString('multi-ok:staging', $chunks);
        $this->assertStringNotContainsString('Undefined constant "STDIN"', $chunks);
    }

    /** @test */
    public function it_returns_command_definition_for_allowed_user(): void
    {
        $runner = $this->app->make(ArtisanRunnerInterface::class);
        $user = $this->fakeUser(false);

        $definition = $runner->definitionForUser($user, 'artisan-runner:demo');

        $this->assertIsArray($definition);
        $this->assertSame('artisan-runner:demo', $definition['name'] ?? $definition['command'] ?? 'artisan-runner:demo');
    }

    /** @test */
    public function it_returns_empty_panel_endpoints_shape(): void
    {
        $runner = $this->app->make(ArtisanRunnerInterface::class);
        $endpoints = $runner->emptyPanelEndpoints();

        $this->assertSame(['commands', 'definition', 'run', 'answer'], array_keys($endpoints));
        $this->assertSame('', $endpoints['commands']);
    }

    private function registerDemoCommand(): void
    {
        $command = new class extends Command
        {
            protected $signature = 'artisan-runner:demo {path?} {--force}';

            protected $description = 'ArtisanRunner demo command';

            public function handle(): int
            {
                $this->line('demo-ok');

                return self::SUCCESS;
            }
        };

        $this->app->make(ConsoleKernel::class)->registerCommand($command);
    }

    private function registerConfirmCommand(): void
    {
        $command = new class extends Command
        {
            protected $signature = 'artisan-runner:confirm';

            protected $description = 'ArtisanRunner confirm stub';

            public function handle(): int
            {
                if (! $this->confirm('Delete duplicate media records?', false)) {
                    $this->line('aborted');

                    return self::SUCCESS;
                }

                $this->line('confirmed');

                return self::SUCCESS;
            }
        };

        $this->app->make(ConsoleKernel::class)->registerCommand($command);
    }

    private function registerMultiPromptCommand(): void
    {
        $command = new class extends Command
        {
            protected $signature = 'artisan-runner:multi-prompt';

            protected $description = 'ArtisanRunner multi-prompt stub';

            public function handle(): int
            {
                if (! $this->confirm('Continue with cleanup?', false)) {
                    $this->line('aborted');

                    return self::SUCCESS;
                }

                $env = (string) $this->ask('Target environment?', 'production');
                $this->line('multi-ok:' . $env);

                return self::SUCCESS;
            }
        };

        $this->app->make(ConsoleKernel::class)->registerCommand($command);
    }

    private function fakeUser(bool $isSuperadmin): Authenticatable
    {
        return new class($isSuperadmin) extends Authenticatable
        {
            public bool $is_superadmin;

            /** @var list<string> */
            public array $roles = [];

            public function __construct(bool $isSuperadmin)
            {
                $this->is_superadmin = $isSuperadmin;
                $this->roles = $isSuperadmin ? ['superadmin'] : ['admin'];
            }

            public function hasRole($roles): bool
            {
                $roles = (array) $roles;

                return count(array_intersect($roles, $this->roles)) > 0;
            }

            public function hasAnyRole(...$roles): bool
            {
                $flat = [];
                foreach ($roles as $role) {
                    foreach ((array) $role as $item) {
                        $flat[] = $item;
                    }
                }

                return count(array_intersect($flat, $this->roles)) > 0;
            }
        };
    }
}
