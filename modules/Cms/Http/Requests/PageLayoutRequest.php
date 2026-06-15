<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Cms\Entities\PageLayout;

class PageLayoutRequest extends FormRequest
{
    /**
     * @var array<string, string>
     */
    protected array $schemaRules = [];

    public function __construct(
        array $rules = [],
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->schemaRules = $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var PageLayout|null $row */
        $row = $this->route('page_layout');
        $ignoreId = $row instanceof PageLayout ? $row->getKey() : $row;
        $layoutBuildersTable = modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');
        $uniqueModel = Rule::unique((new PageLayout)->getTable(), 'target_model_class')->ignore($ignoreId);

        return array_merge($this->schemaRules, [
            'target_model_class' => ['sometimes', 'required', 'string', 'max:512', $uniqueModel],
            'admin_label' => 'nullable|string|max:255',
            'enabled' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
            'layout_builder_id' => 'nullable|integer|exists:' . $layoutBuildersTable . ',id',
            'blade_source' => 'sometimes|string|in:db,filesystem',
            'blade_segments' => 'nullable|array',
            'blade_segments.head' => 'nullable|string',
            'blade_segments.body' => 'nullable|string',
            'blade_segments.footer' => 'nullable|string',
            'blade_view_name' => 'nullable|string|max:512',
        ]);
    }
}
