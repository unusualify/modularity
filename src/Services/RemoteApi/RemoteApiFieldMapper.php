<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Illuminate\Support\Carbon;

class RemoteApiFieldMapper
{
    public function __construct(
        private readonly RemoteApiConfiguration $configuration,
    ) {}

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $existingAttributes
     * @return array<string, mixed>
     */
    public function map(array $row, array $existingAttributes = [], ?callable $transformField = null): array
    {
        $attributes = [];

        foreach ($this->configuration->mapping() as $column => $source) {
            $value = $this->resolveSource($row, (string) $source);
            $attributes[$column] = $transformField
                ? $transformField($column, $value, $row)
                : $value;
        }

        return $this->applyFieldRules($attributes, $existingAttributes);
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $existingAttributes
     * @return array<string, mixed>
     */
    public function applyFieldRules(array $attributes, array $existingAttributes = []): array
    {
        $preserve = array_flip($this->configuration->preserveLocalFields());

        foreach ($this->configuration->fields() as $column => $rules) {
            $isLocal = (bool) ($rules['local'] ?? false);
            $shouldSync = (bool) ($rules['sync'] ?? true);

            if ($isLocal || ! $shouldSync) {
                if (array_key_exists($column, $existingAttributes)) {
                    $attributes[$column] = $existingAttributes[$column];
                } elseif (isset($preserve[$column]) && array_key_exists($column, $existingAttributes)) {
                    $attributes[$column] = $existingAttributes[$column];
                } elseif ($isLocal) {
                    unset($attributes[$column]);
                }
            }
        }

        foreach ($this->configuration->preserveLocalFields() as $column) {
            if (array_key_exists($column, $existingAttributes)) {
                $attributes[$column] = $existingAttributes[$column];
            }
        }

        return $attributes;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function resolveSource(array $row, string $source): mixed
    {
        if ($source === '@raw') {
            return $row;
        }

        if ($source === '@now') {
            return Carbon::now();
        }

        if (str_starts_with($source, '@json:')) {
            $path = mb_substr($source, 6);
            $value = $path === '' ? $row : data_get($row, $path);

            return $value === null ? null : json_encode($value, JSON_THROW_ON_ERROR);
        }

        if (preg_match('/^@cast:([^:]+):(.+)$/', $source, $matches)) {
            $value = data_get($row, $matches[2]);

            return $this->castValue($matches[1], $value);
        }

        return data_get($row, $source);
    }

    private function castValue(string $type, mixed $value): mixed
    {
        return match ($type) {
            'int', 'integer' => $value === null ? null : (int) $value,
            'float', 'double' => $value === null ? null : (float) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => $value === null ? null : (string) $value,
            'array' => is_array($value) ? $value : (array) $value,
            default => $value,
        };
    }
}
