<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\File;
use Unusualify\Modularous\Entities\Model;

trait FilesTrait
{
    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set `passAfterSaveFilesTrait` during pending-only
     * revision saves so {@see afterSaveFilesTrait} is skipped.
     */
    protected bool $pendingBypassRevisionFilesTrait = true;

    public function setColumnsFilesTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/\bfile\b/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        return $columns;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return Model
     */
    public function hydrateFilesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('files')) {
            return $object;
        }

        $object->setRelation('files', $this->getPreviewFiles($object, $fields));

        return $object;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveFilesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('files')) {
            return;
        }

        $object->loadMissing('files');

        foreach ($this->resolveFileTraitRoles($fields) as $role) {
            if (! $this->attachmentRoleIsPresentInFields($fields, $role)) {
                continue;
            }

            $payload = $this->getAttachmentPayloadForRole($fields, $role);
            if ($payload === null) {
                continue;
            }

            if ($this->isAttachmentRoleTranslatedForFields($fields, $role)) {
                $rolePayload = is_array($payload) ? $payload : [];

                foreach (getLocales() as $locale) {
                    if (! array_key_exists($locale, $rolePayload)) {
                        continue;
                    }

                    $slice = $rolePayload[$locale];
                    $this->detachFilesForRoleLocale($object, $role, $locale);

                    if ($slice === null) {
                        continue;
                    }

                    $rows = is_array($slice) ? $slice : [];
                    $this->attachFileSpecsFromRows($object, $rows, $role, $locale);
                }
            } else {
                $locale = (string) config('app.locale', 'en');
                $this->detachFilesForRoleLocale($object, $role, $locale);
                $rows = is_array($payload) ? $payload : [];
                $this->attachFileSpecsFromRows($object, $rows, $role, $locale);
            }
        }
    }

    /**
     * Remove all file pivots for this role + locale so the next attach matches {@code fields} exactly.
     */
    private function detachFilesForRoleLocale($object, string $role, string $locale): void
    {
        $relatedKey = $object->files()->getRelated()->getQualifiedKeyName();
        $ids = $object->files()
            ->wherePivot('role', $role)
            ->wherePivot('locale', $locale)
            ->pluck($relatedKey);

        if ($ids->isEmpty()) {
            return;
        }

        $object->files()->detach($ids->all());
        $this->mustTouchEloquentModel();
    }

    /**
     * @param array<int|string, mixed> $rows
     */
    private function attachFileSpecsFromRows($object, array $rows, string $role, string $locale): void
    {
        $this->collectPivotSpecsForFileRows($object, $rows, $role, $locale)->each(function ($file) use ($object) {
            if (! File::withTrashed()->whereKey($file['file_id'])->exists()) {
                return;
            }

            $object->files()->attach($file['file_id'], Arr::except($file, ['file_id', 'id']));
            $this->mustTouchEloquentModel();
        });
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsFilesTrait($object, $fields, $schema)
    {
        $fileInputs = $this->getColumns(__TRAIT__);
        if (! empty($fileInputs) && $object->has('files')) {
            $schema = $schema ?? $this->inputs();
            $schema = $this->chunkInputs($schema, all: true, noGroupChunk: true);
            $default_locale = config('app.locale');
            $fallback_locale = config('app.fallback_locale');
            $filesByRole = $object->files->groupBy('pivot.role');

            foreach ($fileInputs as $role) {
                if (isset($filesByRole[$role])) {
                    $input = $schema[$role];
                    if ($input['translated'] ?? false) {
                        foreach ($filesByRole[$role]->groupBy('pivot.locale') as $locale => $filesByLocale) {
                            $fields[$role][$locale] = $filesByLocale->map(function ($file) {
                                return $file->mediableFormat();
                            });
                        }
                    } else {
                        $files = $filesByRole[$role]->groupBy('pivot.locale')[$default_locale] ?? $filesByRole[$role]->groupBy('pivot.locale')[$fallback_locale] ?? collect([]);
                        $fields[$role] = $files->map(function ($file) {
                            return $file->mediableFormat();
                        });
                    }
                } else {
                    $input = $schema[$role] ?? null;

                    if ($input) {
                        $fields += [
                            $input['name'] => ($input['translated']) ?? false ? Collection::make(Arr::mapWithKeys(getLocales(), function ($locale) {
                                return [$locale => []];
                            })) : Collection::make([]),
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Preview: merge DB file pivots with payload.
     *
     * @param array<string, mixed> $fields
     */
    private function getPreviewFiles($object, array $fields): Collection
    {
        $object->loadMissing('files');

        $roles = $this->resolveFileTraitRoles($fields);
        $original = $object->files;

        if (! collect($roles)->contains(fn ($role) => $this->attachmentRoleIsPresentInFields($fields, $role))) {
            return $original;
        }

        $out = Collection::make();

        foreach ($roles as $role) {
            if (! $this->attachmentRoleIsPresentInFields($fields, $role)) {
                $out = $out->merge($original->where('pivot.role', $role));

                continue;
            }

            $payload = $this->getAttachmentPayloadForRole($fields, $role);

            if ($this->isAttachmentRoleTranslatedForFields($fields, $role)) {
                $rolePayload = is_array($payload) ? $payload : [];

                foreach (getLocales() as $locale) {
                    if (! array_key_exists($locale, $rolePayload)) {
                        $out = $out->merge($original->filter(
                            fn ($f) => $f->pivot->role === $role && $f->pivot->locale === $locale
                        ));

                        continue;
                    }

                    $slice = $rolePayload[$locale];
                    if ($slice === null) {
                        continue;
                    }

                    $rows = is_array($slice) ? $slice : [];
                    $out = $out->merge($this->pivotSpecsToFileModels(
                        $object,
                        $this->collectPivotSpecsForFileRows($object, $rows, $role, $locale)
                    ));
                }
            } else {
                $rows = is_array($payload) ? $payload : [];
                $locale = (string) config('app.locale', 'en');
                $out = $out->merge($this->pivotSpecsToFileModels(
                    $object,
                    $this->collectPivotSpecsForFileRows($object, $rows, $role, $locale)
                ));
            }
        }

        $out = $out->merge($original->filter(
            fn ($f) => ! in_array($f->pivot->role, $roles, true)
        ));

        return $out->values();
    }

    /**
     * Roles for file pivots only — never image / media-library fields (e.g. {@code photos}).
     *
     * @param array<string, mixed> $fields
     * @return list<string>
     */
    private function resolveFileTraitRoles(array $fields): array
    {
        $resolved = $this->resolveAttachmentRoles(
            __TRAIT__,
            '/\bfile\b/',
            $fields,
            fn ($k, $v) => $this->valueLooksLikeFileRolePayload($v)
        );

        return array_values(array_filter(
            $resolved,
            fn (string $role) => ! $this->shouldExcludeRoleFromFileTrait($role, $fields)
        ));
    }

    /**
     * @param array<int|string, mixed> $rows
     */
    private function collectPivotSpecsForFileRows($object, array $rows, string $role, string $locale): Collection
    {
        $specs = Collection::make();
        $fileablesTable = modularousConfig('tables.fileables', 'um_fileables');

        Collection::make($rows)->each(function ($file) use ($object, $fileablesTable, $specs, $role, $locale) {
            if (! is_array($file) || ! isset($file['id'])) {
                return;
            }

            $fileableId = $object->files()
                ->select($fileablesTable . '.id as pivot_id')
                ->where('file_id', $file['id'])
                ->where('role', $role)
                ->where('locale', $locale)->value('pivot_id') ?? null;

            $specs->push([
                ...($fileableId ? ['id' => $fileableId] : []),
                'file_id' => $file['id'],
                'role' => $role,
                'locale' => $locale,
            ]);
        });

        return $specs;
    }

    private function pivotSpecsToFileModels($object, Collection $specs): Collection
    {
        $filesCollection = Collection::make();

        $specs->each(function ($file) use ($object, $filesCollection) {
            $newFile = File::withTrashed()->find($file['file_id']);
            if (! $newFile) {
                return;
            }

            $pivot = $newFile->newPivot($object, Arr::except($file, ['id']), 'fileables', true);
            $newFile->setRelation('pivot', $pivot);
            $filesCollection->push($newFile);
        });

        return $filesCollection;
    }
}
