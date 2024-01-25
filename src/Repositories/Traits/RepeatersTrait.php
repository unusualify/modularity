<?php

namespace Unusualify\Modularity\Repositories\Traits;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
/**
 * This trait is used for repeaters that may or may not have files or images in them.
 * If there is a file or image in the repeater, it should be removed from the repeater object, to be able to get detected by the FilesTrait and/or ImagesTrait.
 * as of @version 1.0.0, this trait onl manages Medias.
 * @uses name::function Name
 * @author Hazarcan Doğa
 * @version ${1:1.0.0}
 * @since 08 Jan 2024
 */

trait RepeatersTrait
{
    use ImagesTrait, FilesTrait, PricesTrait;


    public function setColumnsRepeatersTrait($columns, $inputs) {

        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($this->inputs())->reduce(function($acc, $curr){
            if(preg_match('/json-repeater/', $curr['type'])){
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
    public function afterSaveRepeatersTrait($object, $fields) {

        if ($this->shouldIgnoreFieldBeforeSave('repeaters')) {
            return $object;
        }
        // $defaultLocale = unusualConfig('locale');
        $defaultLocale = config('app.locale');
        // $locales = getLocales();
        $system_locales = getLocales();

        foreach ($this->getColumns(__TRAIT__) as $name) {
            $input = $this->inputs()[$name];
            $isTranslated = $input['translated'] ?? false;

            if(isset($fields[$name])) {
                $unsetColumns = [];
                Collection::make($this->traitColumns)->each(function($columns, $traitName) use(&$unsetColumns){

                    $unsetColumns = Collection::make($columns)->reduce(function($unsetColumns, $column){
                        if(preg_match('/([A-Za-z-_\.]+)\.\*\.([A-Za-z-_\.]+)/', $column, $matches)){
                            if(!in_array($matches[2], $unsetColumns))
                                $unsetColumns[] = $matches[2];
                        }

                        return $unsetColumns;
                    }, $unsetColumns);


                });

                $intersect_locales = array_intersect(array_keys($fields[$name]), $system_locales);
                $localized = false;
                $exist_locale = null;

                $existingRepeaters = isset($object->id) ? $object->repeaters()
                    ->where('repeatable_id', $object->id)
                    ->where('role', $name)
                    ->get() : null;
                // dd($object->id, $existingRepeaters, $existingRepeaters->where('repeatable_id', $object->id));
                // $repeaterModels = isset($object->id) ? $existingRepeaters->where('repeatable_id', $object->id) : null;

                if(count($intersect_locales) > 1){
                    $localized = true;
                    $exist_locale = $intersect_locales[0];
                }

                foreach($system_locales as $system_locale) {
                    $content = $fields[$name];

                    if($localized)
                        $content = isset($fields[$name][$system_locale]) ? $fields[$name][$system_locale] : $fields[$name][$exist_locale];

                    foreach($unsetColumns as $unsetColumn){
                        foreach ($content as &$item) {
                            # code...
                            unset($item[$unsetColumn]);
                        }
                    }

                    $data = [
                        'role' => $name,
                        'content' => $content,
                        'locale' => $system_locale,
                    ];

                    $existingRepeater = $existingRepeaters ? $existingRepeaters->where('locale', $system_locale)->first() : null;

                    $existingRepeater
                        ? $existingRepeater->update($data)
                        : $object->repeaters()->create($data);

                }
            }
        }

        return;
        foreach($this->getRepeaterInputs() as $repeaterInput) {
            $repeaterName = $repeaterInput['name'] ?? $repeaterInput;

            $repeaterTranslated = $repeaterInput['translated'] ?? false;

            if(isset($fields[$repeaterName])) {
                $repeater = $fields[$repeaterName];

                $existingRepeaters = $object->repeaters()->where('role', $repeaterName)->get();
                // dd($object->id, $existingRepeaters, $existingRepeaters->where('repeatable_id', $object->id));
                $repeaterModels = isset($object->id) ? $existingRepeaters->where('repeatable_id', $object->id) : null;

                // dd($repeater, $repeaterModel, $existingRepeaters);
                // dd($locales, $defaultLocale);
                foreach($locales as $locale) {
                    $saveFormat = [
                        'role' => $repeaterName,
                        'locale' => $locale,
                    ];
                    // dd($repeater, $saveFormat);
                    if($repeaterTranslated) {
                        if(isset($repeater[$locale])) {
                            $saveFormat['content'] = $repeater[$locale];
                        } else {
                            $saveFormat['content'] = $repeater[$defaultLocale];
                            // dd($repeater, $saveFormat, $locale, $defaultLocale);
                        }
                    }
                    else {
                        $saveFormat['content'] = $repeater;
                    }

                    // dd($repeaterModels);
                    $repeaterModel = $repeaterModels ? $repeaterModels->where('locale', $locale)->first() : null;
                    if($repeaterModel) {
                        // dd($repeaterModel, $saveFormat);
                        $repeaterModel->update($saveFormat);
                    } else {
                        // dd($saveFormat);
                        $object->repeaters()->create($saveFormat);
                    }
                }
                /**
                 * these are not correc, keepin em for reference
                 // $repeaterModel = isset($repeaterData['id']) ? $existingRepeaters->where('id', $repeaterData['id'])->first() : null;
                 // $repeater = $fields[$repeaterName];
                 // if($repeaterModel) {
                 //     $repeaterModel->update($repeater);
                 // } else {
                 //     $repeaterModel = $object->repeaters()->create($repeater);
                 // }
                 // foreach($fields[$name] as $index => $repeaterData) {
                     // foreach($repeaterData as $index => $repeater) {
                     // }
                         // dd($repeater, $repeaterData, $index);
                     // dd($existingRepeaters, $repeaterModel, $repeaterData);

                     // Here, we establish the releationship between the repeater and the parent aka object,
                     // the [repeater] fields should be saved as json.
                     // dd($repeaterData, $object->repeaters());
                     // $object->repeaters()->attach($repeaterData);
                     // $object->save();
                     // dd($object, $repeaterData, $object->repeaters);
                 // }
                 */
            }
        }

        return $object;
    }

    /**
     * Clear the relationship between the repeater and the parent(module) aka object.
     *
     * @param \Unusualify\Modularity\Entities\Model $object
     *
     * @return void
     */
    public function afterDelete($object){

    }

    /**
     * From objects input
     */
    public function getRepeaterInputs() {
        return collect($this->inputs())->reduce(function($acc, $curr){
            if(isset($curr['name']) && preg_match('/json-repeater/', $curr['type'])){
                $acc[] = $curr + ['translated' => $curr['translated'] ?? false ];
            }

            return $acc;
        }, []);
    }

    public function getFormFieldsRepeatersTrait($object, $fields, $schema)
    {
        // not possess any repeater data
        if($object->repeaters->isEmpty()){
            $fields += Arr::mapWithKeys($this->getRepeaterInputs(), function($input){
                return [
                    $input['name'] => ($input['translated'] ?? false) ? Arr::mapWithKeys(getLocales(), function($locale){
                        return [ $locale => [] ];
                    }): []
                ];
            });
        }else{

            foreach($object->repeaters->groupBy('locale') as $repeatersByLocale) {

                foreach($repeatersByLocale as $repeater) {
                    // dd($repeater->content);
                    if($this->inputs()[$repeater->role]['translated'] ?? false) {
                        $name = $repeater->role . '.' . $repeater->locale;

                        foreach (Arr::dot($repeater->content) as $notation => $value) {
                            Arr::set($fields, "{$name}.{$notation}", $value);
                        }

                    } else {
                        $name = $repeater->role;
                        foreach (Arr::dot($repeater->content) as $notation => $value) {
                            Arr::set($fields, "{$name}.{$notation}", $value);
                        }
                    }
                }
                // $fields[$repeater->role] = $repeater->content;
            }
        }
        return $fields;
    }
}
