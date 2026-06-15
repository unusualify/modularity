<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\Media;
use Unusualify\Modularous\Entities\Model;

trait ImagesTrait
{
    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set `passAfterSaveImagesTrait` during pending-only
     * revision saves so {@see afterSaveImagesTrait} is skipped.
     */
    protected bool $pendingBypassRevisionImagesTrait = true;

    public function setColumnsImagesTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/image/', $curr['type'])) {
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
    public function hydrateImagesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('medias')) {
            return $object;
        }

        $object->setRelation('medias', $this->getPreviewMedias($object, $fields));

        return $object;
    }

    /**
     * Preview: merge DB medias with payload; omitted roles / locales keep persisted rows.
     *
     * @param  array<string, mixed>  $fields
     */
    private function getPreviewMedias($object, array $fields): Collection
    {
        $object->loadMissing('medias');

        $roles = $this->resolveAttachmentRoles(__TRAIT__, '/image/', $fields, fn ($k, $v) => $this->valueLooksLikeImageRolePayload($v));
        $original = $object->medias;

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
                            fn ($m) => $m->pivot->role === $role && $m->pivot->locale === $locale
                        ));

                        continue;
                    }

                    $slice = $rolePayload[$locale];
                    if ($slice === null) {
                        continue;
                    }

                    $rows = is_array($slice) ? $slice : [];
                    $acc = Collection::make();
                    $specs = $this->pushImage($object, $acc, $rows, $role, $locale);
                    $out = $out->merge($this->pivotSpecsToMediaModels($object, $specs));
                }
            } else {
                $rows = is_array($payload) ? $payload : [];
                $locale = (string) config('app.locale', 'en');
                $acc = Collection::make();
                $specs = $this->pushImage($object, $acc, $rows, $role, $locale);
                $out = $out->merge($this->pivotSpecsToMediaModels($object, $specs));
            }
        }

        $out = $out->merge($original->filter(
            fn ($m) => ! in_array($m->pivot->role, $roles, true)
        ));

        return $out->values();
    }

    /**
     * @return Collection<int, Media>
     */
    private function pivotSpecsToMediaModels($object, Collection $specs): Collection
    {
        $mediasCollection = Collection::make();

        $specs->each(function ($spec) use ($object, $mediasCollection) {
            if (! is_array($spec)) {
                return;
            }

            $mediaId = $spec['media_id'] ?? null;
            $mediaId = is_array($mediaId) ? Arr::first($mediaId) : $mediaId;
            if ($mediaId === null) {
                return;
            }

            $newMedia = Media::withTrashed()->find($mediaId);
            if (! $newMedia) {
                return;
            }

            $pivot = $newMedia->newPivot($object, Arr::except($spec, ['id']), modularousConfig('tables.mediables', 'umod_mediables'), true);
            $newMedia->setRelation('pivot', $pivot);
            $mediasCollection->push($newMedia);
        });

        return $mediasCollection;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveImagesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('medias')) {
            return;
        }

        $object->loadMissing('medias');

        $roles = $this->resolveAttachmentRoles(__TRAIT__, '/image/', $fields, fn ($k, $v) => $this->valueLooksLikeImageRolePayload($v));

        foreach ($roles as $role) {
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
                    $this->detachMediasForRoleLocale($object, $role, $locale);

                    if ($slice === null) {
                        continue;
                    }

                    $rows = is_array($slice) ? $slice : [];
                    $this->attachImageSpecsFromRows($object, $rows, $role, $locale);
                }
            } else {
                $locale = (string) config('app.locale', 'en');
                $this->detachMediasForRoleLocale($object, $role, $locale);
                $rows = is_array($payload) ? $payload : [];
                $this->attachImageSpecsFromRows($object, $rows, $role, $locale);
            }
        }
    }

    /**
     * Remove all media pivots for this role + locale so the next attach matches {@code fields} exactly.
     */
    private function detachMediasForRoleLocale($object, string $role, string $locale): void
    {
        $relatedKey = $object->medias()->getRelated()->getQualifiedKeyName();
        $relation = $object->medias()->wherePivot('role', $role);

        if (modularousConfig('media_library.translated_form_fields', false)) {
            $relation->wherePivot('locale', $locale);
        }

        $ids = $relation->pluck($relatedKey);

        if ($ids->isEmpty()) {
            return;
        }

        $object->medias()->detach($ids->all());
        $this->mustTouchEloquentModel();
    }

    /**
     * @param  array<int|string, mixed>  $rows
     */
    private function attachImageSpecsFromRows($object, array $rows, string $role, string $locale): void
    {
        $acc = Collection::make();
        $specs = $this->pushImage($object, $acc, $rows, $role, $locale);

        $specs->each(function ($media) use ($object) {
            if (! is_array($media) || ! isset($media['media_id'])) {
                return;
            }

            $object->medias()->attach($media['media_id'], Arr::except($media, ['media_id', 'id']));
            $this->mustTouchEloquentModel();
        });
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsImagesTrait($object, $fields, $schema)
    {
        $imageInputs = $this->getColumns(__TRAIT__);
        if (! empty($imageInputs) && $object->has('medias')) {
            $schema = $schema ?? $this->inputs();
            $schema = $this->chunkInputs($schema, all: true, noGroupChunk: true);
            $mediasByRole = $object->medias->groupBy('pivot.role');
            $default_locale = config('app.locale');
            $fallback_locale = config('app.fallback_locale');

            foreach ($imageInputs as $role) {
                if (! isset($schema[$role])) {
                    continue;
                }

                if (isset($mediasByRole[$role])) {
                    $input = $schema[$role];
                    if ($input['translated'] ?? false) {
                        foreach ($mediasByRole[$role]->groupBy('pivot.locale') as $locale => $mediasByLocale) {
                            $fields[$role][$locale] = $mediasByLocale->map(function ($media) {
                                return $media->mediableFormat();
                            });
                        }
                    } else {
                        $medias = $mediasByRole[$role]->groupBy('pivot.locale')[$default_locale] ?? $mediasByRole[$role]->groupBy('pivot.locale')[$fallback_locale] ?? collect([]);
                        $fields[$role] = $medias->map(function ($media) {
                            return $media->mediableFormat();
                        });
                    }
                } else {
                    $input = $schema[$role] ?? null;

                    if ($input) {
                        $fields += [
                            $input['name'] => ($input['translated'] ?? false) ? Collection::make(Arr::mapWithKeys(getLocales(), function ($locale) {
                                return [$locale => []];
                            })) : Collection::make([]),
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    public function pushImage($object, $images, $imagesData, $role, $locale, $index = null)
    {
        $mediablesTable = modularousConfig('tables.mediables', 'um_mediables');
        Collection::make($imagesData)->each(function ($image) use ($object, $mediablesTable, &$images, $role, $locale, $index) {
            if (! is_array($image) || ! isset($image['id'])) {
                return;
            }

            $replacePattern = '/([A-Za-z-_]+)(\.)(\*)(\.)([A-Za-z-_\.]+)/';
            $role = preg_replace($replacePattern, '${1}${2}' . $index . '${4}${5}', $role);
            $mediableId = $object->medias()
                ->select($mediablesTable . '.id as pivot_id')
                ->where('media_id', $image['id'])
                ->where('role', $role)
                ->where('locale', $locale)->value('pivot_id') ?? null;

            $images->push([
                ...($mediableId ? ['id' => $mediableId] : []),
                'media_id' => $image['id'],
                'role' => $role,
                'metadatas' => json_encode($image['metadatas'] ?? []),
                'crop' => 'default',
                'locale' => $locale,
            ]);
        });

        return $images;
    }

    /**
     * @param string $role
     * @return array
     */
    public function getCrops($role)
    {
        return $this->model->mediasParams[$role];
    }
}
