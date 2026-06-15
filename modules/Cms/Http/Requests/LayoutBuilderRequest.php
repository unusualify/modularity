<?php

declare(strict_types=1);

namespace Modules\Cms\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Modules\Cms\Entities\LayoutBuilder;
use Unusualify\Modularous\Http\Requests\Request;

class LayoutBuilderRequest extends Request
{
    /**
     * @return array<string, mixed>
     */
    public function rulesForAll(): array
    {
        $styleSheetsTable = modularousConfig('tables.cms_style_sheets', 'um_cms_style_sheets');
        $layoutBuildersTable = modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:191',
                Rule::unique($layoutBuildersTable, 'slug')->ignore($this->slugIgnoreId()),
            ],
            'blade_source' => 'required|string|in:db,filesystem',
            // 'blade_view_name' => 'exclude_if:blade_source,db|nullable|required_if:blade_source,filesystem|string|max:512',
            // 'blade_segments' => 'exclude_if:blade_source,filesystem|nullable|required_if:blade_source,db|array',
            'blade_segments.head' => 'nullable|string',
            'blade_segments.body' => 'nullable|string',
            'blade_segments.footer' => 'nullable|string',
            'definition' => 'nullable|array',
            'style_sheet_slugs' => 'nullable|array',
            'style_sheet_slugs.*' => 'nullable|string',
            'style_sheet_id' => 'nullable|integer|exists:' . $styleSheetsTable . ',id',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForCreate(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForUpdate(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $bladeSource = (string) ($this->input('blade_source', 'db'));
            $maxBytes = max(4096, (int) modularousConfig('cms_layout_builder.max_blade_segments_bytes', 512_000));

            if ($bladeSource === 'db') {
                $segments = $this->input('blade_segments', []);
                $combined = '';

                if (is_array($segments)) {
                    foreach (['head', 'body', 'footer'] as $key) {
                        if (isset($segments[$key]) && is_string($segments[$key])) {
                            $combined .= $segments[$key];
                        }
                    }
                }

                if ($maxBytes > 0 && mb_strlen($combined) > $maxBytes) {
                    $v->errors()->add('blade_segments', 'Combined Blade segments exceed the configured byte limit.');
                }
            }

            if ($bladeSource === 'filesystem') {
                $name = trim((string) $this->input('blade_view_name', ''));
                if ($name === '') {
                    return;
                }
                if (! View::exists($name)) {
                    $v->errors()->add('blade_view_name', 'The Blade view was not found. Check namespaces and publish stubs if needed.');
                }
            }

            foreach ((array) $this->input('style_sheet_slugs', []) as $slug) {
                if (is_string($slug) && $slug !== '' && preg_match('/[\/\\\\]/', $slug)) {
                    $v->errors()->add('style_sheet_slugs', 'Style sheet slugs must be scalar slug tokens, not paths.');
                }
            }
        });
    }

    /**
     * @return int|string|null Primary key ignored by unique(slug).
     */
    private function slugIgnoreId(): mixed
    {
        $route = $this->route('layout_builder');

        if ($route instanceof LayoutBuilder) {
            return $route->getKey();
        }

        return is_numeric($route) ? $route : $this->route('id');
    }
}
