<?php

namespace Unusualify\Modularous\Repositories\Traits\Concerns;

use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\FilesTrait;

/**
 * Shared helpers for file / image (and similar) payload shapes on {@see Repository}.
 *
 * Payloads may be top-level (`photos`) or nested per locale (`en.photos`) after form preparation.
 */
trait InteractsWithAttachmentPayloads
{
    /**
     * @param callable(string, mixed): bool $inferRoleFromKeyValue
     * @return list<string>
     */
    protected function resolveAttachmentRoles(string $traitFqcn, string $chunkInputTypeRegex, array $fields, callable $inferRoleFromKeyValue): array
    {
        $fromTrait = $this->getColumns($traitFqcn);

        $fromChunk = collect($this->chunkInputs(all: true))
            ->filter(fn ($input) => isset($input['type']) && preg_match($chunkInputTypeRegex, $input['type']))
            ->pluck('name')
            ->all();

        $fromFields = [];
        foreach ($fields as $key => $value) {
            if ($this->reservedAttachmentFieldKey($key)) {
                continue;
            }

            if (in_array($key, getLocales(), true) && is_array($value)) {
                foreach ($value as $subKey => $subVal) {
                    if ($subKey === 'active' || $this->reservedAttachmentFieldKey((string) $subKey)) {
                        continue;
                    }
                    if ($inferRoleFromKeyValue((string) $subKey, $subVal)) {
                        $fromFields[] = (string) $subKey;
                    }
                }

                continue;
            }

            if ($inferRoleFromKeyValue($key, $value)) {
                $fromFields[] = $key;
            }
        }

        return collect($fromTrait)
            ->merge($fromChunk)
            ->merge($fromFields)
            ->unique()
            ->values()
            ->all();
    }

    protected function reservedAttachmentFieldKey(string $key): bool
    {
        return in_array($key, [
            'translations',
            'translationLanguages',
            '_token',
            '_method',
            'revisionId',
            'activeLanguage',
            'preview',
        ], true);
    }

    /**
     * Role appears in the incoming payload (top-level or under a locale bucket).
     */
    protected function attachmentRoleIsPresentInFields(array $fields, string $role): bool
    {
        if (array_key_exists($role, $fields)) {
            return true;
        }

        if(data_get($fields, $role) !== null) {
            return true;
        }

        foreach (getLocales() as $locale) {
            if (array_key_exists($role, $fields[$locale] ?? [])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>|list<mixed>|null
     */
    protected function getAttachmentPayloadForRole(array $fields, string $role): mixed
    {
        if (array_key_exists($role, $fields)) {
            return $fields[$role];
        }

        if(($notation = data_get($fields, $role)) !== null) {
            return $notation;
        }

        $nested = [];
        foreach (getLocales() as $locale) {
            if (array_key_exists($role, $fields[$locale] ?? [])) {
                $nested[$locale] = $fields[$locale][$role];
            }
        }

        return $nested === [] ? null : $nested;
    }

    /**
     * Payload uses locale keys (translated image/file field).
     */
    protected function isLocaleKeyedAttachmentPayload(mixed $payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        foreach (array_keys($payload) as $key) {
            if (in_array($key, getLocales(), true)) {
                return true;
            }
        }

        return false;
    }

    protected function isAttachmentRoleTranslatedInSchema(string $role): bool
    {
        $chunked = $this->chunkInputs(all: true);

        return (bool) ($chunked[$role]['translated'] ?? false);
    }

    /**
     * Translated vs locale-keyed payload when schema is missing (e.g. revision JSON only).
     *
     * @param array<string, mixed> $fields
     */
    protected function isAttachmentRoleTranslatedForFields(array $fields, string $role): bool
    {
        if ($this->isAttachmentRoleTranslatedInSchema($role)) {
            return true;
        }

        $payload = $this->getAttachmentPayloadForRole($fields, $role);
        if (! is_array($payload)) {
            return false;
        }

        return $this->isLocaleKeyedAttachmentPayload($payload);
    }

    /**
     * File / image field payload: either locale => rows or a list of rows with id.
     */
    protected function valueLooksLikeMorphAttachmentPayload(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if ($this->isLocaleKeyedAttachmentPayload($value)) {
            return true;
        }

        foreach ($value as $item) {
            if (is_array($item) && isset($item['id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Media library row (image) — distinguish from file-library rows which also use numeric id.
     */
    protected function arrayLooksLikeMediaLibraryItem(array $item): bool
    {
        if (isset($item['thumbnail']) || isset($item['medium'])) {
            return true;
        }

        $meta = $item['metadatas'] ?? null;
        if (is_array($meta)) {
            $def = $meta['default'] ?? null;
            if (is_array($def) && (array_key_exists('altText', $def) || array_key_exists('video', $def))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Image / media-library payload for inferring roles from raw request data.
     */
    protected function valueLooksLikeImageRolePayload(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if ($this->isLocaleKeyedAttachmentPayload($value)) {
            foreach (getLocales() as $loc) {
                if (! array_key_exists($loc, $value)) {
                    continue;
                }
                $slice = $value[$loc];
                if ($slice === null || ! is_array($slice)) {
                    continue;
                }
                foreach ($slice as $row) {
                    if (is_array($row) && $this->arrayLooksLikeMediaLibraryItem($row)) {
                        return true;
                    }
                }
            }

            return false;
        }

        foreach ($value as $item) {
            if (is_array($item) && $this->arrayLooksLikeMediaLibraryItem($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * File-library payload (excludes media-library / image rows).
     */
    protected function valueLooksLikeFileRolePayload(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if ($this->isLocaleKeyedAttachmentPayload($value)) {
            foreach (getLocales() as $loc) {
                if (! array_key_exists($loc, $value)) {
                    continue;
                }
                $slice = $value[$loc];
                if ($slice === null || $slice === []) {
                    return true;
                }
                if (! is_array($slice)) {
                    continue;
                }
                foreach ($slice as $row) {
                    if (is_array($row) && isset($row['id']) && ! $this->arrayLooksLikeMediaLibraryItem($row)) {
                        return true;
                    }
                }
            }

            return false;
        }

        foreach ($value as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }
            if ($this->arrayLooksLikeMediaLibraryItem($item)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * FilePond field payload: list of rows with a `uuid` (temp folder or persisted row id path).
     */
    protected function valueLooksLikeFilepondRolePayload(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if ($this->isLocaleKeyedAttachmentPayload($value)) {
            foreach (getLocales() as $loc) {
                if (! array_key_exists($loc, $value)) {
                    continue;
                }
                $slice = $value[$loc];
                if ($slice === null || $slice === [] || ! is_array($slice)) {
                    continue;
                }
                foreach ($slice as $row) {
                    if (is_array($row) && isset($row['uuid'])) {
                        return true;
                    }
                }
            }

            return false;
        }

        foreach ($value as $item) {
            if (is_array($item) && isset($item['uuid'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Chunked input is a media-library image field (not file-library).
     */
    protected function isImageLibraryInputRole(string $role): bool
    {
        $input = $this->chunkInputs(all: true)[$role] ?? null;
        if (! is_array($input) || ! isset($input['type'])) {
            return false;
        }

        return preg_match('/\bimage\b/i', (string) $input['type']) === 1;
    }

    /**
     * Exclude from {@see FilesTrait} so media IDs are not written as {@code file_id}.
     *
     * @param array<string, mixed> $fields
     */
    protected function shouldExcludeRoleFromFileTrait(string $role, array $fields): bool
    {
        if ($this->isImageLibraryInputRole($role)) {
            return true;
        }

        $payload = $this->getAttachmentPayloadForRole($fields, $role);
        if (! is_array($payload)) {
            return false;
        }

        return $this->valueLooksLikeImageRolePayload($payload);
    }
}
