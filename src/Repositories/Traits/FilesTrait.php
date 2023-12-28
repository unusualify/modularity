<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;

trait FilesTrait
{
    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Entities\Model
     */
    public function hydrateFilesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('files')) {
            return $object;
        }

        $filesCollection = Collection::make();
        $filesFromFields = $this->getFiles($fields);

        $filesFromFields->each(function ($file) use ($object, $filesCollection) {
            $newFile = File::withTrashed()->find($file['id']);
            $pivot = $newFile->newPivot($object, Arr::except($file, ['id']), 'fileables', true);
            $newFile->setRelation('pivot', $pivot);
            $filesCollection->push($newFile);
        });

        $object->setRelation('files', $filesCollection);

        return $object;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveFilesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('files')) {
            return;
        }

        $object->files()->sync([]);

        $this->getFiles($fields)->each(function ($file) use ($object) {
            $object->files()->attach($file['id'], Arr::except($file, ['id']));
        });
    }

    /**
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    private function getFiles($fields)
    {
        $files = Collection::make();

        $systemLocales = getLocales();

        $fileRoles = $this->getFileColumns();

        foreach($fileRoles as $role){
            if(isset($fields[$role]) && count(array_keys($fields[$role])) > 0){
                $default_locale = array_keys($fields[$role])[0];
                foreach ($systemLocales as $locale) {
                    if(isset($fields[$role][$locale])){
                        Collection::make($fields[$role][$locale])->each(function ($file) use (&$files, $role, $locale) {
                            $files->push([
                                'id' => $file['id'],
                                'role' => $role,
                                'locale' => $locale,
                            ]);
                        });
                    }else {
                        Collection::make($fields[$role][$default_locale])->each(function ($file) use (&$files, $role, $locale) {
                            $files->push([
                                'id' => $file['id'],
                                'role' => $role,
                                'locale' => $locale,
                            ]);
                        });
                    }
                }
                // foreach($fields[$role] as $locale => $filesForRole){
                //     Collection::make($filesForRole)->each(function ($file) use (&$files, $role, $locale) {
                //         $files->push([
                //             'id' => $file['id'],
                //             'role' => $role,
                //             'locale' => $locale,
                //         ]);
                //     });
                // }
            }else{
                dd($role);
            }
        }

        return $files;

        if (isset($fields['medias'])) {
            foreach ($fields['medias'] as $role => $filesForRole) {
                if (Str::contains($role, ['[', ']'])) {
                    $start = strpos($role, '[') + 1;
                    $finish = strpos($role, ']', $start);
                    $locale = substr($role, $start, $finish - $start);
                    $role = strtok($role, '[');
                }

                $locale = $locale ?? config('app.locale');

                if (in_array($role, $this->model->filesColumns ?? [])
                    || in_array($role, $this->getFileColumns() ?: [])
                    || in_array($role, config(unusualBaseKey() . '.block_editor.files', []))) {

                    Collection::make($filesForRole)->each(function ($file) use (&$files, $role, $locale) {
                        $files->push([
                            'id' => $file['id'],
                            'role' => $role,
                            'locale' => $locale,
                        ]);
                    });
                }
            }
        }

        return $files;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsFilesTrait($object, $fields)
    {
        if ($object->has('files')) {
            // foreach ($object->files->groupBy('pivot.role') as $role => $filesByRole) {
            //     foreach ($filesByRole->groupBy('pivot.locale') as $locale => $filesByLocale) {
            //         // $fields['files'][$locale][$role] = $filesByLocale->map(function ($file) {
            //         //     return $file->mediableFormat();
            //         // });
            //         $fields[$role][$locale] = $filesByLocale->map(function ($file) {
            //             return $file->mediableFormat();
            //         });
            //     }
            // }
            $systemLocales = getLocales();
            $filesByRole = $object->files->groupBy('pivot.role');
            foreach ($this->getFileColumns() as $role) {
                if(isset($filesByRole[$role])){
                    foreach ($filesByRole->groupBy('pivot.locale') as $locale => $filesByLocale) {
                        $fields[$role][$locale] = $filesByLocale->map(function ($file) {
                            return $file->mediableFormat();
                        });
                    }
                }else {
                    foreach ($systemLocales as $locale) {
                        $fields[$role][$locale] = [];
                    }
                }
            }
        }
        return $fields;
    }

    public function getFileColumns() {
        return collect($this->inputs())->reduce(function($acc, $curr){
            if(preg_match('/file/', $curr['type'])){
                $acc[] = $curr['name'];
            }
            return $acc;
        }, []);
    }
}
