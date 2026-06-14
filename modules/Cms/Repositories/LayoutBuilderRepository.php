<?php

namespace Modules\Cms\Repositories;

use Illuminate\Support\Str;
use Modules\Cms\Entities\LayoutBuilder;
use Unusualify\Modularous\Repositories\Repository;

class LayoutBuilderRepository extends Repository
{
    public function __construct(LayoutBuilder $model)
    {
        $this->model = $model;
    }

    public function prepareFieldsBeforeCreate($fields)
    {
        $fields = parent::prepareFieldsBeforeCreate($fields);

        if (empty($fields['blade_source']) || ! is_string($fields['blade_source'])) {
            $fields['blade_source'] = (string) modularousConfig('cms_layout_builder.default_blade_source', 'db');
        }

        $fields['blade_segments'] = $this->normalizeSegments($fields['blade_segments'] ?? []);

        $fields['slug'] = $this->normalizeSlug(
            isset($fields['slug']) && is_string($fields['slug']) ? $fields['slug'] : null,
            (string) ($fields['name'] ?? ''),
            null,
        );

        return $this->enforceExclusiveStorageFields($fields);
    }

    /**
     * @param  \Unusualify\Modularous\Models\Model  $object
     */
    public function prepareFieldsBeforeSave($object, $fields)
    {
        $fields = parent::prepareFieldsBeforeSave($object, $fields);

        if (! ($object instanceof LayoutBuilder)) {
            return $fields;
        }

        $name = (string) ($fields['name'] ?? $object->name ?? '');

        if (array_key_exists('slug', $fields)) {
            $incoming = $fields['slug'];
            $fields['slug'] = $this->normalizeSlug(
                is_string($incoming) && $incoming !== '' ? $incoming : null,
                $name,
                $object->getKey(),
            );
        }

        if (array_key_exists('blade_segments', $fields)) {
            $segmentsPayload = $fields['blade_segments'];

            if (is_array($segmentsPayload)) {
                $fields['blade_segments'] = $this->normalizeSegments($segmentsPayload);
            } else {
                // Ignore the null payload emitted while switching to filesystem so stored segments stay intact.
                unset($fields['blade_segments']);
            }
        }

        if (isset($fields['blade_source'])) {
            $src = ($fields['blade_source'] ?: 'db') === 'filesystem' ? 'filesystem' : 'db';
            if ($src === 'filesystem') {
                // Keep existing DB segments so toggling storage doesn't erase drafts.
            } else {
                $fields['blade_view_name'] = null;
            }
        }

        if (isset($fields['style_sheet_slugs']) && ! is_array($fields['style_sheet_slugs'])) {
            $fields['style_sheet_slugs'] = [];
        }

        return $fields;
    }

    /**
     * Strips incompatible payload when admins flip storage mode mid-request.
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function enforceExclusiveStorageFields(array $fields): array
    {
        $source = ($fields['blade_source'] ?? 'db') === 'filesystem' ? 'filesystem' : 'db';

        if ($source === 'db') {
            $fields['blade_view_name'] = null;
        }

        return $fields;
    }

    /**
     * @param  mixed  $raw
     */
    private function normalizeSegments($raw): array
    {
        $out = ['head' => '', 'body' => '', 'footer' => ''];
        if (! is_array($raw)) {
            return $out;
        }
        foreach ($out as $key => $_) {
            if (isset($raw[$key]) && is_string($raw[$key])) {
                $out[$key] = $raw[$key];
            }
        }

        return $out;
    }

    private function normalizeSlug(?string $slug, string $name, mixed $ignoreId): string
    {
        $slug = $slug !== null && $slug !== '' ? Str::slug($slug) : Str::slug($name);
        if ($slug === '') {
            $slug = 'layout';
        }

        $base = $slug;
        $i = 0;
        while (
            LayoutBuilder::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
