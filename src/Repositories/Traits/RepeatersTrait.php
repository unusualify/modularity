<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Repeater;

/**
 * This trait is used for repeaters that may or may not have files or images in them.
 * If there is a file or image in the repeater, it should be removed from the repeater object, to be able to get detected by the FilesTrait and/or ImagesTrait.
 * as of @version 1.0.0, this trait onl manages Medias.
 *
 * @uses name::function Name
 *
 * @author Hazarcan Doğa
 *
 * @version ${1:1.0.0}
 *
 * @since 08 Jan 2024
 */
trait RepeatersTrait
{
    use FilesTrait, ImagesTrait, PricesTrait;

    public function setColumnsRepeatersTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($this->getRawInputs())->reduce(function ($acc, $curr) {
            if (isset($curr['name'])
                && (preg_match('/json-repeater/', $curr['type'] ?? 'default')
                    || preg_match('/json-repeater/', $curr['root'] ?? 'default')
                )
            ) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        return $columns;
    }

    /**
     * Preview / {@see \Unusualify\Modularous\Repositories\Traits\RevisionsTrait::previewForRevision}: populate `repeaters`
     * from the revision payload so presenters and forms see the same shape as after a normal load.
     *
     * @param  Model  $object
     * @param  array<string, mixed>  $fields
     * @return Model
     */
    public function hydrateRepeatersTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('repeaters')) {
            return $object;
        }

        if (! classHasTrait($object, 'Unusualify\Modularous\Entities\Traits\HasRepeaters') || ! method_exists($object, 'repeaters')) {
            return $object;
        }

        $object->setRelation('repeaters', $this->buildPreviewRepeatersRelation($object, $fields));

        return $object;
    }

    /**
     * Mirrors {@see afterSaveRepeatersTrait} field resolution so one {@see Repeater} row exists per role/locale
     * with `content` taken from the payload (unsaved models for preview).
     *
     * @param  array<string, mixed>  $fields
     */
    private function buildPreviewRepeatersRelation(Model $object, array $fields): Collection
    {
        $out = Collection::make();
        $schema = $this->getRawInputs();
        $systemLocales = getLocales();
        $fallbackLocale = app()->getFallbackLocale();

        foreach ($this->getRepeaterInputs($schema) as $input) {
            $name = $input['name'];
            $isTranslated = $input['translated'] ?? false;

            if (! isset($fields[$name]) || ! is_array($fields[$name])) {
                continue;
            }

            $intersectLocales = array_intersect(array_keys($fields[$name]), $systemLocales);
            $localized = count($intersectLocales) > 1;
            $existLocale = $localized ? ($intersectLocales[0] ?? null) : null;

            if ($isTranslated) {
                foreach ($systemLocales as $systemLocale) {
                    $content = $fields[$name];
                    if ($localized) {
                        $content = isset($fields[$name][$systemLocale])
                            ? $fields[$name][$systemLocale]
                            : ($existLocale ? ($fields[$name][$existLocale] ?? []) : []);
                    }
                    if (! is_array($content)) {
                        $content = [];
                    }

                    $out->push($this->makePreviewRepeaterRow($object, $name, $systemLocale, $content));
                }
            } else {
                $payload = $fields[$name];
                if ($localized) {
                    $payload = isset($fields[$name][$fallbackLocale])
                        ? $fields[$name][$fallbackLocale]
                        : ($existLocale ? ($fields[$name][$existLocale] ?? []) : []);
                }
                if (! is_array($payload)) {
                    $payload = [];
                }

                $out->push($this->makePreviewRepeaterRow($object, $name, $fallbackLocale, $payload));
            }
        }

        return $out;
    }

    /**
     * @param  array<int|string, mixed>  $content
     */
    private function makePreviewRepeaterRow(Model $object, string $role, string $locale, array $content): Repeater
    {
        $repeater = new Repeater([
            'role' => $role,
            'locale' => $locale,
            'content' => $content,
            'repeatable_id' => $object->getKey(),
            'repeatable_type' => $object->getMorphClass(),
        ]);
        $repeater->exists = false;

        return $repeater;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return Model
     */
    public function afterSaveRepeatersTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('repeaters')) {
            return $object;
        }

        $systemLocales = getLocales();
        $fallbackLocale = app()->getFallbackLocale();

        $schema = $this->getRawInputs();

        foreach ($this->getColumns(__TRAIT__) as $name) {
            $input = $schema[$name];
            $isTranslated = $input['translated'] ?? false;

            if (isset($fields[$name])) {
                $unsetColumns = [];
                // Collection::make($this->traitColumns)->each(function ($columns, $traitName) use (&$unsetColumns) {
                //     $unsetColumns = Collection::make($columns)->reduce(function ($unsetColumns, $column) {
                //         if (preg_match('/([A-Za-z-_\.]+)\.\*\.([A-Za-z-_\.]+)/', $column, $matches)) {
                //             if (! in_array($matches[2], $unsetColumns)) {
                //                 $unsetColumns[] = $matches[2];
                //             }
                //         }

                //         return $unsetColumns;
                //     }, $unsetColumns);
                // });

                $intersectLocales = array_intersect(array_keys($fields[$name]), $systemLocales);
                $localized = false;
                $existLocale = null;

                $existingRepeaters = isset($object->id) ? $object->repeaters()
                    ->where('repeatable_id', $object->id)
                    ->where('role', $name)
                    ->get() : null;

                if (count($intersectLocales) > 1) {
                    $localized = true;
                    $existLocale = $intersectLocales[0];
                }

                if ($isTranslated) {

                    foreach ($systemLocales as $systemLocale) {
                        $content = $fields[$name];

                        if ($localized) {
                            $content = isset($fields[$name][$systemLocale]) ? $fields[$name][$systemLocale] : $fields[$name][$existLocale];
                        }

                        // foreach ($unsetColumns as $unsetColumn) {
                        //     foreach ($content as &$item) {
                        //         unset($item[$unsetColumn]);
                        //     }
                        // }

                        $data = [
                            'role' => $name,
                            'content' => $content,
                            'locale' => $systemLocale,
                        ];

                        $existingRepeater = $existingRepeaters ? $existingRepeaters->where('locale', $systemLocale)->first() : null;

                        $result = $existingRepeater
                             ? $existingRepeater->update($data)
                             : $object->repeaters()->create($data);

                        if ($result) {
                            $this->mustTouchEloquentModel();
                        }

                    }

                } else {
                    $payload = $fields[$name];

                    if ($localized) {
                        $payload = isset($fields[$name][$fallbackLocale]) ? $fields[$name][$fallbackLocale] : $fields[$name][$existLocale];
                    }

                    // foreach ($unsetColumns as $unsetColumn) {
                    //     foreach ($payload as &$item) {
                    //         unset($item[$unsetColumn]);
                    //     }
                    // }

                    $data = [
                        'role' => $name,
                        'content' => $payload,
                        'locale' => $fallbackLocale,
                    ];

                    $existingRepeater = $existingRepeaters ? $existingRepeaters->where('locale', $fallbackLocale)->first() : null;

                    $result = $existingRepeater
                        ? $existingRepeater->update($data)
                        : $object->repeaters()->create($data);

                    if ($result) {
                        $this->mustTouchEloquentModel();
                    }
                }
            }
        }
    }

    /**
     * From objects input
     */
    public function getRepeaterInputs($schema = null)
    {
        $schema = $schema ?? $this->getRawInputs();

        return collect($schema)->reduce(function ($acc, $curr) {
            if (isset($curr['name']) && (preg_match('/json-repeater/', $curr['root'] ?? 'default') || preg_match('/json-repeater/', $curr['type']))) {
                $acc[$curr['name']] = $curr + ['translated' => $curr['translated'] ?? false];
            } else if ($curr['type'] == 'wrap' && isset($curr['schema'])) {
                $acc = array_merge($acc, $this->getRepeaterInputs($curr['schema']));
            }

            return $acc;
        }, []);
    }

    public function getFormFieldsRepeatersTrait($object, $fields, $schema = null)
    {
        // not possess any repeater data
        if (classHasTrait($object, 'Unusualify\Modularous\Entities\Traits\HasRepeaters') && method_exists($object, 'repeaters')) {
            $schema = $schema ?? $this->getRawInputs();

            $repeaterInputs = $this->getRepeaterInputs($schema);
            if (! empty($repeaterInputs) && ! $object->repeaters()->exists()) {
                $fields += Arr::mapWithKeys($repeaterInputs, function ($input) {
                    return [
                        $input['name'] => ($input['translated'] ?? false) ? Arr::mapWithKeys(getLocales(), function ($locale) {
                            return [$locale => []];
                        }) : ($input['default'] ?? []),
                    ];
                });
            } elseif (! empty($repeaterInputs)) {

                foreach ($object->repeaters->groupBy('locale') as $repeatersByLocale) {
                    foreach ($repeatersByLocale as $repeater) {
                        if ($repeaterInputs[$repeater->role]['translated'] ?? false) {
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
                }
            }
        }

        return $fields;
    }
}
