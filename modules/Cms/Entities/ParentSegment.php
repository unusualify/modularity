<?php

namespace Modules\Cms\Entities;

use Unusualify\Modularous\Entities\Model;

/**
 * One public route binding per model class + locale (unique on target_model_class + locale):
 * URL path prefix registry only. Presentation shells live in {@see PageLayout}.
 *
 * @property string $target_model_class
 * @property string $locale Empty string = all locales
 * @property string $normalized_prefix May be deliberately empty for locale-root URLs (homepage) when enabled.
 * @property string|null $admin_label
 * @property bool $enabled
 * @property int $sort_order
 */
class ParentSegment extends Model
{
    protected $fillable = [
        'target_model_class',
        'locale',
        'normalized_prefix',
        'admin_label',
        'enabled',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getTable(): string
    {
        return modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');
    }
}
