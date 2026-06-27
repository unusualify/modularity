<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiAdapterInterface;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiDtoInterface;

class AbstractRemoteApiAdapter implements RemoteApiAdapterInterface
{
    public function __construct(
        protected readonly RemoteApiConfiguration $configuration,
        protected readonly RemoteApiFieldMapper $fieldMapper,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $existingAttributes
     *
     * @return array<string, mixed>
     */
    public function mapToAttributes(array $row, array $existingAttributes = []): array
    {
        $attributes = $this->fieldMapper->map(
            $row,
            $existingAttributes,
            fn (string $column, mixed $value, array $payload) => $this->transformField($column, $value, $payload)
        );

        return $this->afterMap($attributes, $this->mapToDto($row), $existingAttributes);
    }

    /**
     * @param array<string, mixed> $row
     */
    public function mapToDto(array $row): RemoteApiDtoInterface
    {
        return RemoteApiRecordDto::fromApiRow($row);
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $existingAttributes
     *
     * @return array<string, mixed>
     */
    protected function afterMap(array $attributes, RemoteApiDtoInterface $dto, array $existingAttributes = []): array
    {
        return $attributes;
    }

    /**
     * @param array<string, mixed> $row
     */
    protected function transformField(string $column, mixed $value, array $row): mixed
    {
        return $value;
    }
}
