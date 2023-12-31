<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait MediasTrait
{
    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Entities\Model
     */
    public function hydrateMediasTrait($object, $fields)
    {
        // dd('hydrateMediasTrait', $object, $fields, $this->getMedias($fields));
        if ($this->shouldIgnoreFieldBeforeSave('medias')) {
            return $object;
        }

        $mediasCollection = Collection::make();
        $mediasFromFields = $this->getMedias($fields);

        $mediasFromFields->each(function ($media) use ($object, $mediasCollection) {
            $newMedia = Media::withTrashed()->find(is_array($media['id']) ? Arr::first($media['id']) : $media['id']);
            $pivot = $newMedia->newPivot($object, Arr::except($media, ['id']), config(unusualBaseKey() . '.mediables_table', 'twill_mediables'), true);
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
    public function afterSaveMediasTrait($object, $fields)
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
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    private function getMedias($fields)
    {
        $medias = Collection::make();

        $system_locales = getLocales();

        $medias_roles = $this->getMediaColumns();

        foreach($medias_roles as $role){
            if(isset($fields[$role]) && count(array_keys($fields[$role])) > 0){
                $default_locale = array_keys($fields[$role])[0];
                foreach (getLocales() as $locale) {
                    if(isset($fields[$role][$locale])){

                        Collection::make($fields[$role][$locale])->each(function ($media) use (&$medias, $role, $locale) {
                            $medias->push([
                                'id' => $media['id'],
                                'role' => $role,
                                'metadatas' => json_encode($media['metadatas']),
                                'locale' => $locale,
                            ]);
                        });
                    }else {
                        Collection::make($fields[$role][$default_locale])->each(function ($media) use (&$medias, $role, $locale) {
                            $medias->push([
                                'id' => $media['id'],
                                'role' => $role,
                                'metadatas' => json_encode($media['metadatas']),
                                'locale' => $locale,
                            ]);
                        });
                    }
                }
            }
        }
        // dd($medias);
        return $medias;

        if (isset($fields['medias'])) {
            foreach ($fields['medias'] as $role => $mediasForRole) {
                if (config(unusualBaseKey() . '.media_library.translated_form_fields', false)) {
                    if (Str::contains($role, ['[', ']'])) {
                        $start = strpos($role, '[') + 1;
                        $finish = strpos($role, ']', $start);
                        $locale = substr($role, $start, $finish - $start);
                        $role = strtok($role, '[');
                    }
                }

                $locale = $locale ?? config('app.locale');

                if (in_array($role, array_keys($this->model->mediasParams ?? []))
                    || in_array($role, array_keys(config(unusualBaseKey() . '.block_editor.crops', [])))
                    || in_array($role, array_keys(config(unusualBaseKey() . '.settings.crops', [])))) {
                    Collection::make($mediasForRole)->each(function ($media) use (&$medias, $role, $locale) {
                        $customMetadatas = $media['metadatas']['custom'] ?? [];
                        if (isset($media['crops']) && !empty($media['crops'])) {
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

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsMediasTrait($object, $fields)
    {
        // dd('getFormFieldsMediasTrait', $object,$object->has('medias'), $fields, $this->getMedias($fields));
        $fields['medias'] = null;

        if ($object->has('medias')) {
            foreach ($object->medias->groupBy('pivot.role') as $role => $mediasByRole) {
                foreach ($mediasByRole->groupBy('pivot.locale') as $locale => $mediasByLocale) {
                    $fields[$role][$locale] = $mediasByLocale->map(function ($media) {
                        return $media->mediableFormat();
                    });
            }

                // if (config(unusualBaseKey() . '.media_library.translated_form_fields', false)) {
                //     foreach ($mediasByRole->groupBy('pivot.locale') as $locale => $mediasByLocale) {
                //         foreach ($this->getMediaFormItems($mediasByLocale) as $item) {
                //             $fields[$role][$locale][] = $item;
                //         }
                //     }
                // } else {
                //     foreach ($this->getMediaFormItems($mediasByRole) as $item) {
                //         $fields[$role][] = $item;
                //     }
                // }
            }
        }

        return $fields;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $medias
     * @return array
     */
    private function getMediaFormItems($medias)
    {
        $itemsForForm = [];

        foreach ($medias->groupBy('id') as $id => $mediasById) {
            $item = $mediasById->first();

            $itemForForm = $item->mediableFormat();

            $itemForForm['metadatas']['custom'] = json_decode($item->pivot->metadatas, true);

            foreach ($mediasById->groupBy('pivot.crop') as $crop => $mediaByCrop) {
                $media = $mediaByCrop->first();
                $itemForForm['crops'][$crop] = [
                    'name' => $media->pivot->ratio,
                    'width' => $media->pivot->crop_w,
                    'height' => $media->pivot->crop_h,
                    'x' => $media->pivot->crop_x,
                    'y' => $media->pivot->crop_y,
                ];
            }

            $itemsForForm[] = $itemForForm;
        }

        return $itemsForForm;
    }

    /**
     * @param string $role
     * @return array
     */
    public function getCrops($role)
    {
        return $this->model->mediasParams[$role];
    }
    public function getMediaColumns(){
        // dd(collect($this->inputs()));
        $media_inputs = collect($this->inputs())->reduce(function($acc, $curr){
            if(preg_match('/image/', $curr['type'])){
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        return $media_inputs;
    }
}
