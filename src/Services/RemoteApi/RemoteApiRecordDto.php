<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiDtoInterface;

class RemoteApiRecordDto implements RemoteApiDtoInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly int|string|null $id,
        public readonly array $payload = [],
    ) {}

    public function getRemoteId(): int|string|null
    {
        return $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromApiRow(array $payload): self
    {
        $id = $payload['id'] ?? null;

        return new self($id, $payload);
    }
}
