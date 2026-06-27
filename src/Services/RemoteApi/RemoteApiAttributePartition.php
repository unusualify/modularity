<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Illuminate\Database\Eloquent\Model;

class RemoteApiAttributePartition
{
    /**
     * @param array<string, mixed> $mapped
     * @param array<string, mixed> $existingParentAttributes
     * @return array{local: array<string, mixed>, remote: array<string, mixed>}
     */
    public function partition(
        Model $model,
        RemoteApiConfiguration $configuration,
        array $mapped,
        array $existingParentAttributes = [],
    ): array {
        $preserve = array_flip($configuration->preserveLocalFields());
        $fields = $configuration->fields();
        $fillable = array_flip($model->getFillable());

        $local = [];
        $synced = [];

        foreach ($mapped as $column => $value) {
            if (in_array($column, ['remote_id', 'remote_payload', 'remote_synced_at'], true)) {
                continue;
            }

            $isLocal = (bool) ($fields[$column]['local'] ?? false);

            if ($isLocal || isset($preserve[$column])) {
                if (array_key_exists($column, $existingParentAttributes)) {
                    $local[$column] = $existingParentAttributes[$column];
                } elseif (isset($fillable[$column])) {
                    $local[$column] = $value;
                }

                continue;
            }

            $synced[$column] = $this->normalizeSyncedValue($value);
        }

        foreach ($configuration->preserveLocalFields() as $column) {
            if (array_key_exists($column, $existingParentAttributes) && isset($fillable[$column])) {
                $local[$column] = $existingParentAttributes[$column];
            }
        }

        return [
            'local' => array_intersect_key($local, $fillable),
            'remote' => [
                'remote_id' => $mapped['remote_id'] ?? null,
                'synced_attributes' => $synced,
                'remote_payload' => $this->normalizePayload($mapped['remote_payload'] ?? null),
                'remote_synced_at' => $mapped['remote_synced_at'] ?? now(),
            ],
        ];
    }

    private function normalizeSyncedValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    private function normalizePayload(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }
}
