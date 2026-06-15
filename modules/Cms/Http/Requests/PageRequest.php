<?php

namespace Modules\Cms\Http\Requests;

use Closure;
use Unusualify\Modularous\Http\Requests\Request;

class PageRequest extends Request
{
    public function rulesForAll()
    {
        return [
            'layout' => 'nullable|string|max:120',
            'schema' => 'nullable|array',
            'title' => 'sometimes|required|string|max:255',
            /**
             * Slug input (SlugHydrate) sends `{ slug: string, active: bool }` per locale when manageActive is true;
             * legacy payloads may still send a plain string.
             */
            // 'slug_segment' => [
            //     'sometimes',
            //     'required',
            //     fn (string $attribute, mixed $value, Closure $fail) => $this->validateSlugSegmentLocaleValue($attribute, $value, $fail),
            // ],
            // 'seo_title' => 'nullable|string|max:255',
            // 'seo_description' => 'nullable|string|max:255',
            // 'canonical_url' => 'nullable|url|max:255',
            // 'robots_index' => 'nullable|boolean',
            // 'robots_follow' => 'nullable|boolean',
            'publish_start_date' => 'nullable|date',
            'publish_end_date' => 'nullable|date',
        ];
    }

    public function rulesForCreate()
    {
        return [];
    }

    public function rulesForUpdate()
    {
        return [];
    }

    /**
     * @param Closure(string): void $fail
     */
    protected function validateSlugSegmentLocaleValue(string $attribute, mixed $value, Closure $fail): void
    {
        $text = $this->extractSlugSegmentText($value);
        if ($text === null) {
            $fail(__('validation.string', ['attribute' => $attribute]));

            return;
        }

        if (mb_strlen($text) > 255) {
            $fail(__('validation.max.string', ['attribute' => $attribute, 'max' => 255]));
        }
    }

    protected function extractSlugSegmentText(mixed $value): ?string
    {
        if (is_array($value) && array_key_exists('slug', $value)) {
            $slug = $value['slug'];

            return $slug === null || $slug === '' ? '' : (string) $slug;
        }

        if ($value === null) {
            return '';
        }

        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }
}
