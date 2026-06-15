<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\Filepond as FilepondEntity;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\TemporaryFilepond;
use Unusualify\Modularous\Facades\Filepond as FilepondFacade;

trait FilepondsTrait
{
    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set `passAfterSaveFilepondsTrait` during
     * pending-only revision saves so {@see afterSaveFilepondsTrait} is skipped (live Filepond persistence deferred).
     *
     * Set to false on the repository to run {@see afterSaveFilepondsTrait} even while queuing a pending revision.
     */
    protected bool $pendingBypassRevisionFilepondsTrait = true;

    public function setColumnsFilepondsTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/filepond/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        $columns[$traitName] = array_unique(array_merge($this->traitColumns[$traitName] ?? [], $_columns));

        return $columns;
    }

    /**
     * Preview: merge persisted `fileponds` with payload (revision or form) so FilePond fields reflect pending data.
     * Rows whose UUID still exists only in {@see TemporaryFilepond} (pending approval / bypassed afterSave) are
     * surfaced as unsaved {@see FilepondEntity} models with `isTemporaryRevisionPreview` set.
     *
     * @param Model $object
     * @param array<string, mixed> $fields
     * @return Model
     */
    public function hydrateFilepondsTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('fileponds')) {
            return $object;
        }
        if (! $object->has('fileponds')) {
            return $object;
        }

        $object->setRelation('fileponds', $this->getPreviewFileponds($object, $fields));

        return $object;
    }

    /**
     * @param Model $object
     * @param array<string, mixed> $fields
     */
    private function getPreviewFileponds($object, array $fields): Collection
    {
        $object->loadMissing('fileponds');

        $original = $object->fileponds;
        $out = Collection::make();
        $replacedRoles = [];

        foreach ($this->getColumns(__TRAIT__) as $column) {
            if (! $this->dataHasFilepondPayloadKey($fields, $column)) {
                continue;
            }

            $files = data_get($fields, $column);

            if (preg_match('/\.\*\./', $column)) {
                foreach ($files as $index => $nestedFiles) {
                    $nestedRole = preg_replace('/\.\*\./', ".$index.", $column);
                    $replacedRoles[$nestedRole] = true;

                    if (Arr::isAssoc($nestedFiles)) {
                        foreach ($nestedFiles as $locale => $nestedFilesByLocale) {
                            if (empty($nestedFilesByLocale)) {
                                continue;
                            }
                            $rows = is_array($nestedFilesByLocale) ? $nestedFilesByLocale : [];
                            $out = $out->merge($this->mapFilepondRowsToPreviewModels($object, $rows, $nestedRole, (string) $locale, $original));
                        }
                    } else {
                        if (empty($nestedFiles)) {
                            continue;
                        }
                        $rows = is_array($nestedFiles) ? $nestedFiles : [];
                        $locale = (string) config('app.locale', 'en');
                        $out = $out->merge($this->mapFilepondRowsToPreviewModels($object, $rows, $nestedRole, $locale, $original));
                    }
                }
            } else {
                $role = $column;
                $replacedRoles[$role] = true;

                if (Arr::isAssoc($files)) {
                    foreach ($files as $locale => $filesByLocale) {
                        if (empty($filesByLocale)) {
                            continue;
                        }
                        $rows = is_array($filesByLocale) ? $filesByLocale : [];
                        $out = $out->merge($this->mapFilepondRowsToPreviewModels($object, $rows, $role, (string) $locale, $original));
                    }
                } else {
                    $rows = is_array($files) ? $files : [];
                    $locale = (string) config('app.locale', 'en');
                    $out = $out->merge($this->mapFilepondRowsToPreviewModels($object, $rows, $role, $locale, $original));
                }
            }
        }

        foreach ($original as $filepond) {
            if (isset($replacedRoles[$filepond->role])) {
                continue;
            }
            $out->push($filepond);
        }

        return $out->values();
    }

    /**
     * @param array<int|string, mixed> $rows
     */
    private function mapFilepondRowsToPreviewModels(Model $object, array $rows, string $role, string $locale, Collection $original): Collection
    {
        $acc = Collection::make();

        foreach ($rows as $item) {
            if (! is_array($item) || empty($item['uuid'])) {
                continue;
            }

            $uuid = (string) $item['uuid'];

            $existing = $original->first(
                fn (FilepondEntity $f) => $f->role === $role
                    && (string) $f->locale === $locale
                    && $f->uuid === $uuid
            );

            if ($existing) {
                $existing->isTemporaryRevisionPreview = false;
                $acc->push($existing);

                continue;
            }

            $fileName = (string) ($item['file_name'] ?? ($item['file']['name'] ?? ''));

            $preview = new FilepondEntity([
                'uuid' => $uuid,
                'file_name' => $fileName,
                'role' => $role,
                'locale' => $locale,
                'filepondable_id' => $object->getKey(),
                'filepondable_type' => $object->getMorphClass(),
            ]);
            $preview->exists = false;
            $preview->isTemporaryRevisionPreview = $this->filepondUuidIsTemporaryForPreview($uuid, $object, $original);
            $acc->push($preview);
        }

        return $acc;
    }

    /**
     * True when the upload is still only in {@see TemporaryFilepond} (typical when revision workflow deferred afterSave).
     */
    private function filepondUuidIsTemporaryForPreview(string $uuid, Model $object, Collection $originalFileponds): bool
    {
        $persistedOnSubject = $originalFileponds->contains(
            fn (FilepondEntity $f) => $f->uuid === $uuid
                && (int) $f->filepondable_id === (int) $object->getKey()
        );

        if ($persistedOnSubject) {
            return false;
        }

        return TemporaryFilepond::where('folder_name', $uuid)->exists();
    }

    /**
     * Same presence rules as {@see afterSaveFilepondsTrait}: allow empty list (cleared field); skip only when absent.
     *
     * @param array<string, mixed> $fields
     */
    private function dataHasFilepondPayloadKey(array $fields, string $column): bool
    {
        return data_get($fields, $column) !== null;
    }

    public function afterSaveFilepondsTrait($object, $fields)
    {
        $columns = $this->getColumns(__TRAIT__);

        foreach ($columns as $column) {
            $files = data_get($fields, $column) ?? null;

            if (! $files) {
                continue;
            }

            $role = $column;
            if (preg_match('/\.\*\./', $column)) {
                foreach ($files as $index => $nestedFiles) {
                    $nestedRole = preg_replace('/\.\*\./', ".$index.", $column);
                    if (Arr::isAssoc($nestedFiles)) {
                        foreach ($nestedFiles as $locale => $nestedFilesByLocale) {
                            if (empty($nestedFilesByLocale)) {
                                continue;
                            }
                            FilepondFacade::saveFile($object, $nestedFilesByLocale, $nestedRole, $locale);
                            $this->mustTouchEloquentModel();
                        }
                    } else {
                        if (empty($nestedFiles)) {
                            continue;
                        }
                        FilepondFacade::saveFile($object, $nestedFiles, $nestedRole);
                        $this->mustTouchEloquentModel();
                    }
                }
            } else {

                if (Arr::isAssoc($files)) {
                    foreach ($files as $locale => $filesByLocale) {
                        if (empty($filesByLocale)) {
                            continue;
                        }
                        FilepondFacade::saveFile($object, $filesByLocale, $role, $locale);
                        $this->mustTouchEloquentModel();
                    }
                } else {
                    FilepondFacade::saveFile($object, $files, $role);
                    $this->mustTouchEloquentModel();
                }
            }
        }
    }

    public function getFormFieldsFilepondsTrait($object, $fields, $schema)
    {
        $columns = $this->getColumns(__TRAIT__);

        if (count($columns) > 0 && $object->has('fileponds')) {
            $filepondsByRole = $object->fileponds->groupBy('role');
            $default_locale = config('app.locale');
            $locales = getLocales();

            foreach ($this->getColumns(__TRAIT__) as $role) {

                if (isset($filepondsByRole[$role])) {
                    if (isset($schema[$role])) {
                        $input = $schema[$role];
                        if ($input['translated'] ?? false) {
                            $groupedByLocale = $filepondsByRole[$role]->groupBy('locale');
                            foreach ($locales as $locale) {
                                $fields[$role][$locale] = isset($groupedByLocale[$locale])
                                    ? $groupedByLocale[$locale]->map(function ($filepond) use ($object) {
                                        return $filepond->mediableFormat() + [
                                            'id' => $object->id,
                                        ];
                                    })
                                    : [];
                            }
                        } else {
                            $groupedByLocale = $filepondsByRole[$role]->groupBy('locale');
                            $locale = isset($groupedByLocale[$default_locale]) ? $default_locale : $groupedByLocale->keys()->first();
                            $fields[$role] = $groupedByLocale[$locale]->map(function ($filepond) use ($object) {
                                return $filepond->mediableFormat() + [
                                    'id' => $object->id,
                                ];
                            });
                        }
                    }
                } else {
                    $input = $this->inputs()[$role] ?? $schema[$role] ?? null;

                    if ($input) {
                        $fields += [
                            $input['name'] => ($input['translated']) ?? false ? Arr::mapWithKeys(getLocales(), function ($locale) {
                                return [$locale => []];
                            }) : [],
                        ];
                    }
                }
            }
        }

        return $fields;
    }
}
