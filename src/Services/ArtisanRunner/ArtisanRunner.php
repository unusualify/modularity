<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner;

use Illuminate\Contracts\Auth\Authenticatable;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\CommandCatalogInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\CommandNotAllowedException;
use Unusualify\Modularous\Services\ArtisanRunner\Streaming\InteractiveQuestionBroker;

final class ArtisanRunner implements ArtisanRunnerInterface
{
    public function __construct(
        private readonly CommandCatalogInterface $catalog,
        private readonly CommandDefinitionInspector $inspector,
        private readonly CommandExecutor $executor,
        private readonly InteractiveQuestionBroker $questionBroker,
    ) {}

    public function userCanAccess(Authenticatable $user): bool
    {
        if (! modularousConfig('artisan_runner.enabled', false)) {
            return false;
        }

        /** @var list<string> $roles */
        $roles = array_values(array_filter(
            (array) modularousConfig('artisan_runner.allowed_roles', ['superadmin'])
        ));

        if ($roles === []) {
            return false;
        }

        if ($this->catalog->isSuperadmin($user) && in_array('superadmin', $roles, true)) {
            return true;
        }

        if (is_callable([$user, 'hasAnyRole'])) {
            return (bool) $user->hasAnyRole($roles);
        }

        if (is_callable([$user, 'hasRole'])) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function catalogForUser(Authenticatable $user): array
    {
        $this->assertPanelAccess($user);

        return $this->catalog->forUser($user);
    }

    public function definitionForUser(Authenticatable $user, string $command): array
    {
        $this->assertPanelAccess($user);
        $this->assertCommandAllowed($user, $command);

        $resolved = $this->catalog->findForUser($user, $command);
        if ($resolved === null) {
            throw new CommandNotAllowedException("Command [{$command}] is not available.");
        }

        return $this->inspector->inspect($resolved);
    }

    public function run(
        Authenticatable $user,
        string $command,
        array $arguments,
        array $options,
        string $runId,
        callable $emit,
    ): int {
        $this->assertPanelAccess($user);
        $this->assertCommandAllowed($user, $command);

        $emit('started', ['runId' => $runId, 'command' => $command]);

        try {
            $exitCode = $this->executor->execute(
                $command,
                $arguments,
                $options,
                $runId,
                $emit,
            );
        } catch (ArtisanRunnerException $e) {
            $emit('error', ['message' => $e->getMessage()]);
            $emit('done', ['runId' => $runId, 'exitCode' => 1, 'error' => $e->getMessage()]);

            return 1;
        } catch (\Throwable $e) {
            $emit('error', ['message' => $e->getMessage()]);
            $emit('done', ['runId' => $runId, 'exitCode' => 1, 'error' => $e->getMessage()]);

            return 1;
        }

        $emit('done', ['runId' => $runId, 'exitCode' => $exitCode]);

        return $exitCode;
    }

    public function answerPrompt(string $runId, string $promptId, mixed $answer): void
    {
        $this->questionBroker->publishAnswer($runId, $promptId, $answer);
    }

    public function assertCommandAllowed(Authenticatable $user, string $command): void
    {
        if (! $this->catalog->isAllowed($user, $command)) {
            throw new CommandNotAllowedException("Command [{$command}] is not allowed for this user.");
        }
    }

    /**
     * @return array{commands: string, definition: string, run: string, answer: string}
     */
    public function panelEndpoints(): array
    {
        $prefix = Modularous::getAdminRouteNamePrefix() . '.';

        return [
            'commands' => route($prefix . 'artisan-runner.commands'),
            'definition' => route($prefix . 'artisan-runner.commands.show', ['name' => '__NAME__']),
            'run' => route($prefix . 'artisan-runner.runs'),
            'answer' => route($prefix . 'artisan-runner.runs.answer', ['runId' => '__RUN_ID__']),
        ];
    }

    /**
     * @return array{commands: string, definition: string, run: string, answer: string}
     */
    public function emptyPanelEndpoints(): array
    {
        return [
            'commands' => '',
            'definition' => '',
            'run' => '',
            'answer' => '',
        ];
    }

    private function assertPanelAccess(Authenticatable $user): void
    {
        if (! $this->userCanAccess($user)) {
            throw new ArtisanRunnerException('Artisan Runner is disabled or you are not allowed to access it.');
        }
    }
}
