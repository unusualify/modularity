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

        // dd($this->getMedias($fields));

        // dd($fields);
        $this->getMedias($fields)->each(function ($media) use ($object) {
            $object->medias()->attach($media['id'], Arr::except($media, ['id']));
        });
        // dd($object->medias()->get());
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
        // dd($fields, $medias_roles);
        foreach($medias_roles as $role){
            if(Arr::get($fields, $role) != null && count(array_keys(Arr::get($fields, $role))) > 0){
                $default_locale  = unusualConfig('locale');
                $role_ = $role;
                $item_is_translated = true;
                $parent_is_translated = false;
                $parent_locale = $default_locale;
                if(Str::contains($role_, '.')) {
                    $item_is_translated = false;
                    $parent_key = Str::before($role_, '.');
                    $parent = $this->inputs()[$parent_key];
                    $schema =$parent['schema'];
                    $item_key = Str::afterLast($role_, '.');
                    $parent_is_translated = $parent['translated'] ?? false;
                    $parent_locale =  Str::before(Str::after($role_, $parent_key.'.'),'.');
                    // dd($parent_locale);
                    if($parent_is_translated) {
                        $item_is_translated = false;
                    } else {
                        $item_is_translated = $schema[$item_key]['translated'];
                    }
                }
                // continue;
                // dd($item_is_translated, $role, $role_, $fields);
                if($item_is_translated) {
                    // continue;
                    foreach ($system_locales as $locale) {
                        // dd($this->inputs(), $this->chunkInputs($this->inputs()), $fields);
                        if(isset(Arr::get($fields, $role)[$locale])){
                            if(empty(Arr::get($fields, $role)[$locale])) {
                                continue;
                            }
                            Collection::make(Arr::get($fields, $role)[$locale])->each(function ($media) use (&$medias, $role, $locale) {
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
                } else {
                    // dd($fields);
                    // dd(Arr::get($fields, $role), $role, $fields);
                    Collection::make(Arr::get($fields, $role))->each(function ($media) use (&$medias, $role, $parent_locale) {
                        // dd($media, $medias);
                        $medias->push([
                            'id' => $media['id'],
                            'role' => $role,
                            'metadatas' => json_encode($media['metadatas']),
                            'locale' => $parent_locale,
                        ]);
                    });
                }

            }
            else {
                dd('no media for role: ' . $role);
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
    public function getFormFieldsMediasTrait($object, $fields, $schema)
    {
        // dd($object->medias->groupBy('pivot.role'));

        $t = [];
        if ($object->has('medias')) {
            // dd($object->medias->groupBy('pivot.role'));
            foreach ($object->medias->groupBy('pivot.role') as $role => $mediasByRole) {
                // dd($mediasByRole);
                // dd($role, $mediasByRole, $mediasByRole->groupBy('pivot.locale'));
                foreach ($mediasByRole->groupBy('pivot.locale') as $locale => $mediasByLocale) {
                    $role_ = $role;
                    // $fields[$role][$locale] = $mediasByLocale->map(function ($media) {
                    //     return $media->mediableFormat();
                    // });
                    if(Str::contains($role_, '.')) {
                        $parent = Str::before($role_, '.');
                        if( $schema[$parent]['translated'] ?? false) {
                            $after = Str::after($role_, '.');
                            $role_ = $parent .'.' . $locale . '.' . $after;
                        }
                        // dd($role);
                    }
                    $role_ = $role_ . '.' . $locale;

                    $t[] = $role_;
                    Arr::set($fields, "{$role_}", $mediasByLocale->map(function ($media) {
                        return $media->mediableFormat();
                    }));

                    // dd($fields);
                    // $mediasByLocale->each(function ($media) use (&$fields, $role, $locale, $schema) {
                    //     // dd($media);
                    //     if(Str::contains($role, '.')) {
                    //         $parent = Str::before($role, '.');
                    //         if($schema[$parent]['translated']) {
                    //             $after = Str::after($role, '.');
                    //             $role = $parent .'.' . $locale . '.' . $after;
                    //         }
                    //     }
                    //     $role = $role . '.' . $locale;
                    //     // dd( $media->mediableFormat());
                    //     // dd($role);
                    //     Arr::set($fields, "{$role}", $media->mediableFormat());
                    // });
                    // $fields[$role][$locale] = $mediasByLocale->map(function ($media) {
                    //     Arr::set($fields, "{$role}.{$locale}", $media->mediableFormat());
                    //     return $media->mediableFormat();
                    // });
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
        // $fields['medias'] = null;

        // if ($object->has('medias')) {
        //     foreach ($object->medias->groupBy('pivot.role') as $role => $mediasByRole) {
        //         foreach ($mediasByRole->groupBy('pivot.locale') as $locale => $mediasByLocale) {
        //             $fields[$role][$locale] = $mediasByLocale->map(function ($media) {
        //                 return $media->mediableFormat();
        //             });
        //         }
        //     }
        // }
        // dd($fields, $t);
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
            } else if (preg_match('/repeater/', $curr['type']) && isset($this->pivotedRepeatersTrait)) {
                // dd($this->pivotedRepeatersTrait); // to get the image and file names
                $acc = array_merge($acc, $this->pivotedRepeatersTrait);
            }
            return $acc;
        }, []);
        // dd($media_inputs);

        return $media_inputs;
    }
}
