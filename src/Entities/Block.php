<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Unusualify\Modularity\Entities\Traits\HasFiles;
use Unusualify\Modularity\Entities\Traits\HasImages;
use Unusualify\Modularity\Entities\Traits\HasPresenter;
use Unusualify\Modularity\Entities\Traits\Secondary\HasRelated;

class Block extends BaseModel
{
    use HasFiles, HasImages, HasPresenter, HasRelated;

    public $timestamps = false;

    protected $fillable = [
        'blockable_id',
        'blockable_type',
        'position',
        'content',
        'type',
        'child_key',
        'parent_id',
        'editor_name',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    protected $with = ['medias'];

    public function scopeEditor($query, $name = 'default')
    {
        return $name === 'default' ?
            $query->where('editor_name', $name)->orWhereNull('editor_name') :
            $query->where('editor_name', $name);
    }

    public function blockable()
    {
        return $this->morphTo();
    }

    public function children()
    {
        return $this->hasMany('Unusualify\Modularity\Entities\Block', 'parent_id');
    }

    public function input($name)
    {
        return $this->content[$name] ?? null;
    }

    public function translatedInput($name, $forceLocale = null)
    {
        $value = $this->content[$name] ?? null;

        $locale = $forceLocale ?? (
            config('translatable.use_property_fallback', false) && (! array_key_exists(app()->getLocale(), $value ?? []))
            ? config('translatable.fallback_locale')
            : app()->getLocale()
        );

        return $value[$locale] ?? null;
    }

    public function browserIds($name)
    {
        return isset($this->content['browsers']) ? ($this->content['browsers'][$name] ?? []) : [];
    }

    public function checkbox($name)
    {
        return isset($this->content[$name]) && ($this->content[$name][0] ?? $this->content[$name] ?? false);
    }

    public function getPresenterAttribute()
    {
        if (($presenter = modularityConfig('block_editor.block_presenter_path')) != null) {
            return $presenter;
        }

        return null;
    }

    public function getTable()
    {
        return modularityConfig('blocks_table', 'twill_blocks');
    }
}
