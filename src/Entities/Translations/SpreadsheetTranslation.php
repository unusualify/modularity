<?php

namespace Unusualify\Modularity\Entities\Translations;

use  Unusualify\Modularity\Entities\Spreadsheet;
use Unusualify\Modularity\Entities\Model;

class SpreadsheetTranslation extends Model
{
    protected $baseModuleModel = Spreadsheet::class;

    protected $fillable = ['content', 'locale'];

    protected $casts = [
        'content' => 'array',
    ];
}
