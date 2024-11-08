<?php

namespace Modules\SystemUtility\Entities;

use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;

class State extends \Unusualify\Modularity\Entities\State
{
    use HasTranslation, ModelHelpers, IsTranslatable;

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	// protected $fillable = [
	// 	'published',
	// 	'code',
    //     'icon',
    //     'color'
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
