<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\Console\Command\Command;

interface CommandCatalogInterface
{
    /**
     * @return list<array{name: string, description: string}>
     */
    public function forUser(Authenticatable $user): array;

    public function findForUser(Authenticatable $user, string $name): ?Command;

    public function isAllowed(Authenticatable $user, string $name): bool;

    public function isSuperadmin(Authenticatable $user): bool;
}
