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
        $t = [];
        if ($object->has('medias')) {
            // dd($object->medias->groupBy('pivot.role'));
            foreach ($object->medias->groupBy('pivot.role') as $role => $mediasByRole) {
                foreach ($mediasByRole->groupBy('pivot.locale') as $locale => $mediasByLocale) {

                    $role_ = $role;

                    $t[] = $role_;
                    Arr::set($fields, "{$role_}", $mediasByLocale->map(function ($media) {
                        return $media->mediableFormat();
                    }));
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

        $system_locales = getLocales();

        // $default_locale = unusualConfig('locale');
        $default_locale = config('app.locale');

        foreach ($this->getColumns(__TRAIT__) as $role) {
            // foreach(['repeater_name.*.participantImage'] as $role){
            // foreach(['repeater_name.en.*.participantImage', 'repeater_name.tr.*.participantImage'] as $role){
            $imagesArray = data_get($fields, $role);

            // input checking for translated in dot notation
            if (preg_match('/([A-Za-z-_]+)\.([a-z]{2})\.\*\.([A-Za-z-_\.]+)/', $role, $matches)) {
                $parent_input_name = $matches[1];
                $locale = $matches[2];

                foreach ($imagesArray as $index => $data) {
                    $images = $this->pushImage($images, $data, $role, $locale, $index);
                }
                // if(empty($imagesArray)){
                //     dd(
                //         $locale,
                //         $role,

                //     );
                // }else{

                // }

            } elseif (preg_match('/([A-Za-z-_]+)\.\*\.([A-Za-z-_\.]+)/', $role, $matches)) { // dot notation without translated field
                foreach ($system_locales as $key => $locale) {
                    foreach ($imagesArray as $index => $data) {
                        $images = $this->pushImage($images, $data, $role, $locale, $index);
                    }
                }
            } else {
                foreach ($system_locales as $key => $locale) {
                    $imagesData = [];
                    if (isset($imagesArray[$locale])) { // checking whether related locale exists or not
                        $imagesData = $imagesArray[$locale];
                    } elseif (count($intersectLocales = array_intersect(array_keys($imagesArray), $system_locales)) > 0) { // checking at least whether one of related locales exists or not
                        $localeFound = $intersectLocales[0];
                        $imagesData = $imagesArray[$localeFound];
                    } else { // no locales exist on array
                        $imagesData = $imagesArray;
                    }

                    $images = $this->pushImage($images, $imagesData, $role, $locale);
                }
            }
        }

        return $images;

        if (isset($fields['medias'])) {
            foreach ($fields['medias'] as $role => $mediasForRole) {
                if (unusualConfig('media_library.translated_form_fields', false)) {
                    if (Str::contains($role, ['[', ']'])) {
                        $start = mb_strpos($role, '[') + 1;
                        $finish = mb_strpos($role, ']', $start);
                        $locale = mb_substr($role, $start, $finish - $start);
                        $role = strtok($role, '[');
                    }
                }

                $locale = $locale ?? config('app.locale');

                if (in_array($role, array_keys($this->model->mediasParams ?? []))
                    || in_array($role, array_keys(unusualConfig('block_editor.crops', [])))
                    || in_array($role, array_keys(unusualConfig('settings.crops', [])))) {
                    Collection::make($mediasForRole)->each(function ($media) use (&$medias, $role, $locale) {
                        $customMetadatas = $media['metadatas']['custom'] ?? [];
                        if (isset($media['crops']) && ! empty($media['crops'])) {
                            foreach ($media['crops'] as $cropName => $cropData) {
                                $medias->push([
                                    'id' => $media['id'],
                                    'crop' => $cropName,
                                    'role' => $role,
                                    'locale' => $locale,
                                    'ratio' => $cropData['name'],
                                    'crop_w' => $cropData['width'],
                                    'crop_h' => $cropData['height'],
                                    'crop_x' => $cropData['x'],
                                    'crop_y' => $cropData['y'],
                                    'metadatas' => json_encode($customMetadatas),
                                ]);
                            }
                        } else {
                            foreach ($this->getCrops($role) as $cropName => $cropDefinitions) {
                                $medias->push([
                                    'id' => $media['id'],
                                    'crop' => $cropName,
                                    'role' => $role,
                                    'locale' => $locale,
                                    'ratio' => Arr::first($cropDefinitions)['name'],
                                    'crop_w' => null,
                                    'crop_h' => null,
                                    'crop_x' => null,
                                    'crop_y' => null,
                                    'metadatas' => json_encode($customMetadatas),
                                ]);
                            }
                        }
                    });
                }
            }
        }

        return $medias;
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
