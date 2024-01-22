<?php

namespace Unusualify\Modularity\Repositories\Traits;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
/**
 * This trait is used for repeaters that may or may not have files or images in them.
 * If there is a file or image in the repeater, it should be removed from the repeater object, to be able to get detected by the FilesTrait and/or MediasTrait.
 * as of @version 1.0.0, this trait onl manages Medias.
 * @uses name::function Name
 * @author Hazarcan Doğa
 * @version ${1:1.0.0}
 * @since 08 Jan 2024
 */

trait RepeatersTrait
{
    // use MediasTrait;
    private $saveToPivotTable = [
        'image',
        'file',
    ];
    private $staticFieldKeys = [
        'id',
        // 'position',
    ];
    public $pivotedRepeatersTrait;
    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * iterate over inputs and check if there is a repeater,
     * if there is a repeater check if there is a file or image in the repeater
     * if there is a file or image in the repeater, remove it from the repeater object,
     * add it to the parent of the repeater aka $fields with the name of [repeatername].[index].[fieldname],
     */
    public function prepareFieldsBeforeSaveRepeatersTrait($object,$fields) {
        $repeaterInputs = $this->getRepeaterFields();
        $pivotedInputNames = $this->getPivotedInputNames($repeaterInputs);
        foreach($repeaterInputs as $repeaterInput) {
            $repeaterName = $repeaterInput['name'] ?? $repeaterInput;
            $repeaterTranslated = $repeaterInput['translated'] ?? false;
            // dd($repeaterTranslated, $fields[$repeaterName]);
            if(isset($fields[$repeaterName])) {
                if($repeaterTranslated) {
                    $fields = $this->prepareTranslated($fields, $repeaterName, $repeaterInputs, $pivotedInputNames);
                } else {
                    $fields = $this->prepareUntranslated($fields, $repeaterName, $repeaterInputs, $pivotedInputNames);

                }
                // foreach($fields[$repeaterName] as $index => $repeater) {
                //     $formated_repeater = [];
                //         foreach($repeater as $key => $value) {
                //             // dd($key, $value);
                //             if(in_array($key, $pivotedInputNames[$repeaterName])) {
                //                 $this->pivotedRepeatersTrait[] = $repeaterName . '.' . $index . '.' . $key;
                //                 $fields[$repeaterName  . '.' . $index . '.' . $key] = $value;
                //                 // unset($fields[$repeaterName][$index][$key]);
                //                 // clears sepearetd from the original repeater that will be formatted
                //                 unset($repeater[$key]);
                //             }
                //         }
                //     /**
                //      * Arr only part separates the static fields from the fields that will be asssigned to ($content [castsTo: object] in the repeater model)
                //      * array_flip() is essential for key-based comparison in array_diff_key().
                //      * It enables filtering elements based on key presence or absence.
                //      * This approach is useful for keeping dynamic or non-static elements in $repeater while removing those with specific keys
                //      * (information from Bard)
                //      */
                //     // dd($fields[$repeaterName],$fields[$repeaterName][$index]);
                //     unset($fields[$repeaterName][$index]);
                //     dd($repeater, $index);
                //     $fields[$repeaterName]["content"][$index] = $repeater;

                //     // dd($repeaterTranslated, $fields, $repeater);
                //     //    if($repeaterTranslated) {
                //     //        foreach ($locales as $locale) {
                //     //            $fields[$repeaterName]["content"][$locale][$index] = $repeater;
                //     //         }
                //     //     } else {
                //     //         $fields[$repeaterName]["content"][$index] = $repeater;
                //     //     }
                // }
                // $fields[$repeaterName] += [
                //     'role' => $repeaterName,
                // ];
            }
        }
        // dd($fields);
        // dd($fields, Arr::get($originalFields,'repeatername.1.participantImage'), $this->inputs());
        return $fields;
    }

    private function prepareTranslated(array $fields, string $name, $repeaterInputs, $pivotedInputNames) : array{
        $repeaterInputs = $this->getRepeaterFields();
        $pivotedInputNames = $this->getPivotedInputNames($repeaterInputs);

        foreach($fields[$name] as $locale => $repeater) {
            foreach($repeater as $rii => $repeaterItem) {
                // dd( $repeaterItem);
                foreach($repeaterItem as $field => $value) {
                    // dd($key, $value);
                    if(in_array($field, $pivotedInputNames[$name])) {
                        $pivoted_key = $name  . '.' . $locale . '.' . $rii . '.' . $field;
                        $this->pivotedRepeatersTrait[] = $pivoted_key;
                        $fields[$pivoted_key] = $value;
                        unset($repeaterItem[$field]);
                    }
                }
                // unset($fields[$name][$locale][$rii]);
                $fields[$name][$locale][$rii] = $repeaterItem;
            }
            // $fields[$name][$locale] += [
            //     'role' => $name,
            // ];
        }
        // dd($this->pivotedRepeatersTrait);
        return $fields;
    }
    private function prepareUntranslated(array $fields, string $name, $repeaterInputs, $pivotedInputNames) : array{
        $repeaterInputs = $this->getRepeaterFields();
        $pivotedInputNames = $this->getPivotedInputNames($repeaterInputs);

        foreach($fields[$name] as $rii => $repeaterItem) {
            foreach($repeaterItem as $field => $value) {
                // dd($key, $value);
                if(in_array($field, $pivotedInputNames[$name])) {
                    $this->pivotedRepeatersTrait[] = $name . '.' . $rii . '.' . $field;
                    $fields[$name  . '.' . $rii . '.' . $field] = $value;
                    unset($repeaterItem[$field]);
                }
            }
            $fields[$name][$rii] = $repeaterItem;
            // $fields[$name][$locale] += [
            //     'role' => $name,
            // ];
        }
        // dd($fields);
        return $fields;
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
        // dd( $fields);
        $defaultLocale = unusualConfig('locale');
        $locales = getLocales();

        foreach($this->getRepeaterFields() as $repeaterInput) {
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

    private function saveTranslated($repeater, $name, $existingRepeaters, $object) {
        $defaultLocale = unusualConfig('locale');
        $locales = getLocales();
        $repeaterModel = isset($repeater['id']) ? $existingRepeaters->where('id', $repeater['id'])->first() : null;
        foreach($locales as $locale) {
            $saveFormat = [
                'role' => $name,
                'locale' => $locale,
            ];
            if(isset($repeater[$locale])) {
                $saveFormat['content'] = $repeater[$locale];
            } else {
                $saveFormat['content'] = $repeater[$defaultLocale];
            }
            if($repeaterModel) {
                $repeaterModel->update($saveFormat);
            } else {
                $repeaterModel = $object->repeaters()->create($saveFormat);
            }
        }
    }
    private function saveUnranslated($repeater, $name, $existingRepeaters, $object) {
        $locales = getLocales();
        $repeaterModel = isset($repeater['id']) ? $existingRepeaters->where('id', $repeater['id'])->first() : null;
        foreach($locales as $locale) {
            $saveFormat = [
                'role' => $name,
                'locale' => $locale,
                'content' => $repeater
            ];
            if($repeaterModel) {
                $repeaterModel->update($saveFormat);
            } else {
                $repeaterModel = $object->repeaters()->create($saveFormat);
            }
        }
    }
    /**
     * Clear the relationship between the repeater and the parent(module) aka object.
     * @param \Unusualify\Modularity\Entities\Model $object
     * @return void
     */
    public function afterDelete($object)
    {

    }


    /**
     * From objects input
     */
    public function getRepeaterFields() {

        return collect($this->inputs())->reduce(function($acc, $curr){
            return collect($this->inputs())->reduce(function($acc, $curr){
                if(preg_match('/repeater/', $curr['type']) || preg_match('/custom-input-repeater/', $curr['type']))
                {
                    $acc[] = [ 'name' =>$curr['name'], 'translated' => $curr['translated'] ?? false ];
                }
                return $acc;
            }, []);
            return $acc;
        }, []);
    }


    public function getPivotedInputNames($repeaterInputs) {
        $pivotedInputNames = [];
        // dd($repeaterNames);
        // dd($this->inputs(),Arr::get($this->inputs(), 'repeatername.*.participantCompany'));
        // dd($this->inputs(),Arr::hasAny($this->inputs(), 'repeatername'));
        foreach($repeaterInputs as $repeaterData) {
            $pivotedInputNames[$repeaterData['name']] = collect($this->inputs()[$repeaterData['name']]["schema"])->reduce(function($acc, $curr){
                // dd($acc, $curr, $this->saveToPivotTable);
                if(in_array($curr['type'], $this->saveToPivotTable)) {
                    $acc[] = $curr['name'];
                }
                return $acc;
            }, []);
        }
        // foreach($repeaterNames as $repeaterName) {
        //     // Ensure $repeaterName is a string or an integer
        //     $key = is_object($repeaterName) ? $repeaterName->property : (is_array($repeaterName) ? $repeaterName[0] : $repeaterName);
        //     $pivotedInputNames[$key] = collect($this->inputs()[$key]["schema"])->reduce(function($acc, $curr){
        //         if(in_array($curr['type'], $this->saveToPivotTable)) {
        //             $acc[] = $curr['name'];
        //         }
        //         return $acc;
        //     }, []);
        // }
        return $pivotedInputNames;
    }
    public function getFormFieldsRepeatersTrait($object, $fields, $schema)
    {
        foreach($object->repeaters->groupBy('locale') as $repeatersByLocale) {
            foreach($repeatersByLocale as $repeater) {
                // dd($repeater->content);
                if($schema[$repeater->role]['translated'] ?? false) {
                    // dd($repeater->role, $repeater->locale, $repeater->content);
                    Arr::set($fields, $repeater->role . '.' . $repeater->locale, $repeater->content);
                } else {
                    Arr::set($fields, $repeater->role, $repeater->content);
                }
            }
            // $fields[$repeater->role] = $repeater->content;
        }
        // dd($fields);
        return $fields;
    }
}
