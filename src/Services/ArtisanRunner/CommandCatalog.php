<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\CommandCatalogInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Support\AllowlistMatcher;

final class CommandCatalog implements CommandCatalogInterface
{
    public function __construct(
        private readonly AllowlistMatcher $allowlistMatcher = new AllowlistMatcher,
    ) {}

    public function forUser(Authenticatable $user): array
    {
        $items = [];

        foreach ($this->allCommands() as $name => $command) {
            if (! $this->isAllowed($user, $name)) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'description' => (string) $command->getDescription(),
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $items;
    }

    public function findForUser(Authenticatable $user, string $name): ?Command
    {
        if (! $this->isAllowed($user, $name)) {
            return null;
        }

        $commands = $this->allCommands();

        return $commands[$name] ?? null;
    }

    public function isAllowed(Authenticatable $user, string $name): bool
    {
        if (! array_key_exists($name, $this->allCommands())) {
            return false;
        }

        if ($this->isSuperadmin($user)) {
            return true;
        }

        /** @var list<string> $allowlist */
        $allowlist = array_values(array_filter(
            (array) modularousConfig('artisan_runner.allowlist', [])
        ));

        if ($allowlist === []) {
            return false;
        }

        return $this->allowlistMatcher->matches($name, $allowlist);
    }

    public function isSuperadmin(Authenticatable $user): bool
    {
        if (isset($user->is_superadmin) && $user->is_superadmin) {
            return true;
        }

        if (is_callable([$user, 'hasRole'])) {
            return (bool) $user->hasRole('superadmin');
        }

        return false;
    }

    /**
     * @return array<string, Command>
     */
    private function allCommands(): array
    {
        $commands = [];

        foreach (Artisan::all() as $name => $command) {
            if (! $command instanceof Command) {
                continue;
            }

            if ($command->isHidden()) {
                continue;
            }

            $commands[$name] = $command;
        }

        return $commands;
    }
}
