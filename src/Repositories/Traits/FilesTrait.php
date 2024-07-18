<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

trait FilesTrait
{

    public function setColumnsFilesTrait($columns, $inputs) {

        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function($acc, $curr){
            if(preg_match('/\bfile\b/', $curr['type'])){
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
            //     } d
            // }
            $systemLocales = getLocales();
            $default_locale = config('app.locale');
            $filesByRole = $object->files->groupBy('pivot.role');



            foreach ($this->getColumns(__TRAIT__) as $role) {
                if(isset($filesByRole[$role])){
                    $input = $this->inputs()[$role];
                    if($input['translated'] ?? false){
                        foreach ($filesByRole[$role]->groupBy('pivot.locale') as $locale => $filesByLocale) {
                            $fields[$role][$locale] = $filesByLocale->map(function ($file) {
                                return $file->mediableFormat();
                            });
                        }
                    }else{
                        $fields[$role] = $filesByRole[$role]->groupBy('pivot.locale')[$default_locale]->map(function($file){
                            return $file->mediableFormat();
                        });
                    }
                }else {
                    $input = $this->inputs()[$role] ?? null;

                    if($input)
                        $fields += [
                            $input['name'] => ($input['translated']) ?? false ? Arr::mapWithKeys(getLocales(), function($locale){
                                return [ $locale => [] ];
                            }): []
                        ];

                    // foreach ($systemLocales as $locale) {
                    //     $fields[$role][$locale] = [];
                    // }
                }
            }
        }
        return $fields;
    }

    /**
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    private function getFiles($fields)
    {
        $files = Collection::make();

        $systemLocales = getLocales();

        $fileRoles = $this->getColumns(__TRAIT__);

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
                        Collection::make($fields[$role])->each(function ($file) use (&$files, $role, $locale) {
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
                // dd($role);
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
                    || in_array($role, $this->setColumnsFilesTrait() ?: [])
                    || in_array($role, unusualConfig('block_editor.files', []))) {

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


}
