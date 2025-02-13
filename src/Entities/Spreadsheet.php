<?php

namespace Unusualify\Modularity\Entities;

use Unusualify\Modularity\Entities\Model;
use Modules\PressRelease\Entities\Report;
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class Spreadsheet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasTranslation;

    public $translatedAttributes = [
        'content'
    ];

    protected $fillable = [
        'published',
        // 'content',
        'role',
        'locale'
    ];

    protected $casts = [
        'content' => 'array'
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = $this->getTable();
        parent::__construct($attributes);
    }

    public function getTable()
    {
        return modularityConfig('tables.spreadsheets', 'modularity_spreads');
    }
}
