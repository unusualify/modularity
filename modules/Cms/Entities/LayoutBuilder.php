<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Cms\Support\StylesheetManager;
use Unusualify\Modularous\Entities\Model;

/**
 * @property string|null $blade_source {@code db} stores {@see $blade_segments}; {@code filesystem} uses {@see $blade_view_name}.
 * @property array<string,string>|null $blade_segments Keys: {@code head}, {@code body}, {@code footer}.
 * @property array<string,mixed>|null $definition Arbitrary manifest (e.g. {@code cms_page_layout_body_compose}: {@code append}|{@code wrap} for DB body nesting).
 * @property list<string>|null $style_sheet_slugs Additional stylesheet slugs merged after {@see $style_sheet_id}.
 * @property string|null $blade_view_name Passed to Laravel {@see view()} when {@code filesystem}.
 * @property int|null $style_sheet_id FK to {@see StyleSheet}; primary sheet consumed by {@see StylesheetManager::linkHrefsForLayoutBuilder()}.
 */
class LayoutBuilder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'blade_source',
        'blade_segments',
        'blade_view_name',
        'definition',
        'style_sheet_slugs',
        'style_sheet_id',
    ];

    protected $casts = [
        'blade_segments' => 'array',
        'definition' => 'array',
        'style_sheet_slugs' => 'array',
    ];

    /**
     * @return BelongsTo<StyleSheet, LayoutBuilder>
     */
    public function styleSheet(): BelongsTo
    {
        return $this->belongsTo(StyleSheet::class);
    }

    public function getTable(): string
    {
        return modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');
    }
}
