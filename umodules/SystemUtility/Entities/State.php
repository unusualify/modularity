<?php

namespace Modules\SystemUtility\Entities;

use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class State extends \Unusualify\Modularity\Entities\State
{
    use HasTranslation, ModelHelpers, IsTranslatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [x
    // 	'published',
    // 	'code',
    // ];

    /**
     * The translated attributes that are assignable for hasTranslation Trait.
     *
     * @var array<int, string>
     */
    // public $translatedAttributes = [
    // 	'name',
    // 	'active'
    // ];

    // protected function isTranslationModel(): bool
    // {
    //     return Str::endsWith(get_class($this), 'Translation');
    // }
}
