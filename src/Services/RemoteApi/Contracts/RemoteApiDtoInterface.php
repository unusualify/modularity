<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi\Contracts;

interface RemoteApiDtoInterface
{
    public function getRemoteId(): int|string|null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromApiRow(array $payload): self;
}
