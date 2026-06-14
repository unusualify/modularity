<?php

namespace Modules\Cms\Entities;

use Unusualify\Modularous\Entities\Model;

class StyleSheet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'driver',
        'framework_version',
        'framework_source',
        'definition',
        'scss_source',
        'compiled_disk',
        'compiled_path',
        'compiled_checksum',
        'compiled_at',
        'compiler_version',
    ];

    protected $casts = [
        'definition' => 'array',
        'compiled_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return modularousConfig('tables.cms_style_sheets', 'um_cms_style_sheets');
    }
}
