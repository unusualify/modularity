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

        $files_roles = $this->getFileColumns();


        foreach($files_roles as $role){
            if(isset($fields[$role])){
                foreach($fields[$role] as $locale => $filesForRole){
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
        // dd($object, $fields, $this->getFileColumns());
        // $fields['files'] = null;

        // dd(
        //     $this->getFilesColumns()
        // );
        if ($object->has('files')) {
            foreach ($object->files->groupBy('pivot.role') as $role => $filesByRole) {
                // dd($role, $filesByRole);
                foreach ($filesByRole->groupBy('pivot.locale') as $locale => $filesByLocale) {
                    // $fields['files'][$locale][$role] = $filesByLocale->map(function ($file) {
                    //     return $file->mediableFormat();
                    // });
                    $fields[$role][$locale] = $filesByLocale->map(function ($file) {
                        return $file->mediableFormat();
                    });
                }
            }
        }
        return $fields;
    }

    public function getFileColumns(){
        $moduleName = null;
        $routeName = null;
        if( preg_match('/[M|m]{1}odules[\/\\\]([A-Za-z]+)[\/\\\]/', get_class($this), $matches)){
            $moduleName = $matches[1];
        }
        if( preg_match('/(\w+)Repository/', get_class_short_name($this), $matches)){
            $routeName = snakeCase($matches[1]);
        }

        if( $moduleName && $routeName){
            $module = Modularity::find($moduleName);
            $route_config = $module->getRouteConfig($routeName);

            $file_inputs = collect($route_config['inputs'])->reduce(function($acc, $curr){
                if(preg_match('/file/', $curr['type'])){
                    $acc[] = $curr['name'];
                }

                return $acc;
            }, []);

            return $file_inputs;
            //     // $module->getRouteConfig($routeName),
            //     // Module::disable('Testify'),
            //     // config('modules'),
            //     // $module->getParentRoute(),
            //     // $module->allEnabledRoutes(),
            //     // $module->disableRoute('Currency'),
            //     // $module_config,
            //     // $route_config
            // );
        }

        return false;
    }
}
