<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Media;

trait ImagesTrait
{
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
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Entities\Model
     */
    public function hydrateImagesTrait($object, $fields)
    {
        // dd('hydrateImagesTrait', $object, $fields, $this->getMedias($fields));
        if ($this->shouldIgnoreFieldBeforeSave('medias')) {
            return $object;
        }

        $mediasCollection = Collection::make();

        $mediasFromFields = $this->getMedias($fields);

        $mediasFromFields->each(function ($media) use ($object, $mediasCollection) {
            $newMedia = Media::withTrashed()->find(is_array($media['id']) ? Arr::first($media['id']) : $media['id']);
            $pivot = $newMedia->newPivot($object, Arr::except($media, ['id']), unusualConfig('tables.mediables', 'umod_mediables'), true);
            $newMedia->setRelation('pivot', $pivot);
            $mediasCollection->push($newMedia);
        });

        $object->setRelation('medias', $mediasCollection);

        return $object;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveImagesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('medias')) {
            return;
        }

        $object->medias()->sync([]);

        $this->getMedias($fields)->each(function ($media) use ($object) {
            $object->medias()->attach($media['id'], Arr::except($media, ['id']));
        });
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsImagesTrait($object, $fields, $schema)
    {
        // $t = [];
        if ($object->has('medias')) {
            $mediasByRole = $object->medias->groupBy('pivot.role');
            $default_locale = config('app.locale');

            foreach ($this->getColumns(__TRAIT__) as $role) {
                if (isset($mediasByRole[$role])) {
                    $input = $this->inputs()[$role];
                    if ($input['translated'] ?? false) {
                        foreach ($mediasByRole[$role]->groupBy('pivot.locale') as $locale => $mediasByLocale) {
                            $fields[$role][$locale] = $mediasByLocale->map(function ($media) {
                                return $media->mediableFormat();
                            });
                        }
                    } else {
                        $fields[$role] = $mediasByRole[$role]->groupBy('pivot.locale')[$default_locale]->map(function ($media) {
                            return $media->mediableFormat();
                        });
                    }
                } else {
                    $input = $this->inputs()[$role] ?? null;

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

    /**
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    private function getMedias($fields)
    {
        $images = Collection::make();

        $systemLocales = getLocales();

        $imageRoles = $this->getColumns(__TRAIT__);

        foreach($imageRoles as $role){
            if(isset($fields[$role])){
                foreach ($systemLocales as $locale) {
                    if (isset($fields[$role][$locale])) {
                        $images = $this->pushImage($images, $fields[$role][$locale], $role, $locale);
                    } else {
                        $images = $this->pushImage($images, $fields[$role], $role, $locale);

                    }
                }
            }
        }

        return $images;
    }

    public function pushImage($images, $imagesData, $role, $locale, $index = null)
    {

        Collection::make($imagesData)->each(function ($image) use (&$images, $role, $locale, $index) {
            $replacePattern = '/([A-Za-z-_]+)(\.)(\*)(\.)([A-Za-z-_\.]+)/';
            $images->push([
                'id' => $image['id'],
                // 'role' => $role,
                'role' => preg_replace($replacePattern, '${1}${2}' . $index . '${4}${5}', $role),
                'metadatas' => json_encode($image['metadatas']),
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
