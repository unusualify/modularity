<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi\Contracts;

use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;

interface RemoteApiConnectorInterface
{
    public function configuration(): RemoteApiConfiguration;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchList(array $query = []): array;

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOne(int|string $remoteId, array $query = []): ?array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listCatalog(?string $catalogKey = null, array $query = []): array;

    /**
     * @param array<string, mixed> $existingAttributes
     *
     * @return array<string, mixed>
     */
    public function mapRow(array $row, array $existingAttributes = []): array;

    public function clearCache(int|string|null $remoteId = null): void;

    public function resetRequestStats(): void;

    /**
     * @return array{total: int, by_url: array<string, int>}
     */
    public function flushRequestStats(): array;
}
