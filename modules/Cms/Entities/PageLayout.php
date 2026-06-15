<?php

namespace Modules\Cms\Entities;

use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\Model;

/**
 * Locale-agnostic public presentation shell attached to exactly one CMS module-route model ({@see \Modules\Cms\Entities\Concerns\HasPageLayout}).
 *
 * URLs / parent prefixes remain {@see ParentSegment}.
 *
 * @property string $target_model_class Unique FQCN of the routed model
 * @property string|null $admin_label
 * @property bool $enabled
 * @property int $sort_order
 * @property int|null $layout_builder_id
 * @property string|null $blade_source Db segments vs filesystem view (default: db)
 * @property array<string, string>|null $blade_segments Base shell segments when blade_source is db
 * @property string|null $blade_view_name View name when blade_source is filesystem
 */
class PageLayout extends Model
{
    protected $fillable = [
        'target_model_class',
        'admin_label',
        'enabled',
        'sort_order',
        'layout_builder_id',
        'blade_source',
        'blade_segments',
        'blade_view_name',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sort_order' => 'integer',
        'layout_builder_id' => 'integer',
        'blade_segments' => 'array',
    ];

    protected $appends = [
        'blade_segments_summary',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<LayoutBuilder, PageLayout>
     */
    public function layoutBuilder() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LayoutBuilder::class);
    }

    public function getBladeSegmentsSummaryAttribute(): ?string
    {
        $segments = $this->blade_segments;
        if (! is_array($segments)) {
            return null;
        }

        $parts = [];

        foreach (['head', 'body', 'footer'] as $segment) {
            $value = $segments[$segment] ?? null;
            if (! is_string($value)) {
                continue;
            }

            $preview = Str::of($value)->trim();
            if ($preview->isEmpty()) {
                continue;
            }

            $parts[] = ucfirst($segment) . ': ' . (string) $preview->limit(40, '…');
        }

        return empty($parts) ? null : implode(' | ', $parts);
    }

    public function getTable(): string
    {
        return modularousConfig('tables.cms_page_layouts', 'um_cms_page_layouts');
    }
}
