<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi\Contracts;

interface RemoteApiAdapterInterface
{
    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $existingAttributes
     *
     * @return array<string, mixed>
     */
    public function mapToAttributes(array $row, array $existingAttributes = []): array;

    /**
     * @param array<string, mixed> $row
     */
    public function mapToDto(array $row): RemoteApiDtoInterface;
}
