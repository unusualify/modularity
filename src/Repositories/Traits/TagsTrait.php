<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Models\Model;

trait TagsTrait
{
    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set `passAfterSaveTagsTrait` during pending-only
     * revision saves so {@see afterSaveTagsTrait} is skipped.
     */
    protected bool $pendingBypassRevisionTagsTrait = true;

    public function setColumnsTagsTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/tagger/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        $columns[$traitName] = array_unique(array_merge($this->traitColumns[$traitName] ?? [], $_columns));

        return $columns;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveTagsTrait($object, $fields)
    {
        $schema = $this->getRawInputs() ?? [];

        if (! isset($fields['bulk_tags']) && ! isset($fields['previous_common_tags'])) {
            $tagsSchema = $schema['tags'] ?? [];
            $tagsExists = array_key_exists('tags', $fields);
            if (! $this->shouldIgnoreFieldBeforeSave('tags') && $tagsExists) {
                $translated = $tagsSchema['translated'] ?? false;

                $values = $fields['tags'] ?? [];
                if ($translated || (is_array($values) && Arr::isAssoc($values))) {
                    foreach ($values as $locale => $value) {
                        $object->setLocaleTags(tags: $value, locale: $locale);
                    }
                } else {
                    $object->setTags($fields['tags'] ?? []);
                }
            }

        } else {
            if (! $this->shouldIgnoreFieldBeforeSave('bulk_tags')) {
                $previousCommonTags = $fields['previous_common_tags']->pluck('name')->toArray();

                if (! empty($previousCommonTags)) {
                    if (! empty($difference = array_diff($previousCommonTags, $fields['bulk_tags'] ?? []))) {
                        $object->untag($difference);
                    }
                }

                $object->tag($fields['bulk_tags'] ?? []);
            }
        }
    }

    public function getFormFieldsTagsTrait($object, $fields, $schema = null)
    {
        if ($object->has('tags')) {
            $locales = getLocales();

            foreach ($this->getColumns(__TRAIT__) as $column) {
                $translated = false;

                $tagInput = $schema[$column] ?? [];

                $translated = $tagInput['translated'] ?? false;

                if ($translated) {
                    $fields[$column] = $object->tags->groupBy('locale')->map(function ($group) {
                        return $group->map(fn ($tag) => $tag->name);
                    });

                    foreach ($locales as $locale) {
                        $fields[$column][$locale] = $fields[$column][$locale] ?? ($tagInput['default'] ?? ($tagInput['multiple'] ?? true) ? collect([]) : null);
                    }
                } else {
                    $fields[$column] = $object->tags->map(fn ($tag) => $tag->name);
                }
            }
        }

        return $fields;
    }

    protected function filterTagsTrait(&$query, &$scopes)
    {
        $this->addRelationFilterScopeByRelationName($query, $scopes, 'tag_id', 'tags');
    }

    protected function getTagsQuery()
    {
        return $this->model->allTags()->orderBy('count', 'desc');
    }

    /**
     * @param string $query
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTags($query = '', $ids = [], $translated = false, ?callable $map = null)
    {
        $tagQuery = $this->getTagsQuery();

        if (! empty($query)) {
            $tagQuery->where('slug', 'like', '%' . $query . '%');
        }

        if (! empty($ids)) {
            foreach ($ids as $id) {
                $tagQuery->whereHas('tagged', function ($query) use ($id) {
                    $query->where('taggable_id', $id);
                });
            }
        }

        $result = $tagQuery->get();

        if ($translated) {
            $result = $result->groupBy('locale');

            $locales = getLocales();

            foreach ($locales as $locale) {
                $result[$locale] = $result[$locale] ?? collect([]);
            }

            if ($map) {
                $result = $result->map(function ($group) use ($map) {
                    return $group->map($map);
                });
            }
        } elseif ($map) {
            $result = $result->map($map);
        }

        return $result;
    }

    /**
     * @return Collection
     */
    public function getTagsList()
    {
        return $this->getTagsQuery()->where('count', '>', 0)->select('name', 'id')->get()->map(function ($tag) {
            return [
                'label' => $tag->name,
                'value' => $tag->id,
            ];
        });
    }
}
