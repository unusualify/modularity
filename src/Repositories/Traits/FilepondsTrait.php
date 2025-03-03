<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\Filepond;

trait FilepondsTrait
{
    public function setColumnsFilepondsTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/filepond/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        $columns[$traitName] = array_unique(array_merge($this->traitColumns[$traitName] ?? [], $_columns));

        return $columns;
    }

    public function afterSaveFilepondsTrait($object, $fields)
    {
        $columns = $this->getColumns(__TRAIT__);

        foreach ($columns as $role) {
            $files = data_get($fields, $role) ?? null;

            if(!$files){
                continue;
            }

            if (Arr::isAssoc($files)) {
                foreach ($files as $locale => $filesByLocale) {
                    Filepond::saveFile($object, $filesByLocale, $role, $locale);
                }
            } else {
                Filepond::saveFile($object, $files, $role);
            }

            // Filepond::saveFile($object, $files, $role);
            if ($files) {
                // Filepond::saveFile($object, $files, $role);
            }
        }
    }

    public function _beforeSaveFilepondsTrait($object, $fields)
    {
        $columns = $this->getColumns(__TRAIT__);
        dd($columns, $fields);
        foreach ($columns as $role) {
            $files = data_get($fields, $role);
            if ($files) {
                dd($role, $files, $fields);
                // $this->saveFilePond($value, $object, $fields);
                // $this->saveFilePond($object, $files, $role);
                Filepond::saveFile($object, $files, $role);
            }
        }
        dd($columns);
    }

    public function getFormFieldsFilepondsTrait($object, $fields, $schema)
    {
        $columns = $this->getColumns(__TRAIT__);

        if (count($columns) > 0 && $object->has('fileponds')) {
            $filepondsByRole = $object->fileponds->groupBy('role');
            $default_locale = config('app.locale');
            $locales = getLocales();

            foreach ($this->getColumns(__TRAIT__) as $role) {

                if (isset($filepondsByRole[$role])) {
                    $input = $schema[$role];

                    if ($input['translated'] ?? false) {
                        $groupedByLocale = $filepondsByRole[$role]->groupBy('locale');
                        foreach ($locales as $locale) {
                            $fields[$role][$locale] = isset($groupedByLocale[$locale])
                                ? $groupedByLocale[$locale]->map(function ($filepond) use ($object) {
                                    return $filepond->mediableFormat() + [
                                        'id' => $object->id,
                                    ];
                                })
                                : [];
                        }
                    } else {
                        $groupedByLocale = $filepondsByRole[$role]->groupBy('locale');
                        $locale = isset($groupedByLocale[$default_locale]) ? $default_locale : $groupedByLocale->keys()->first();
                        $fields[$role] = $groupedByLocale[$locale]->map(function ($filepond) use ($object) {
                            return $filepond->mediableFormat() + [
                                'id' => $object->id,
                            ];
                        });
                    }
                } else {
                    $input = $this->inputs()[$role] ?? $schema[$role] ?? null;

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
}
