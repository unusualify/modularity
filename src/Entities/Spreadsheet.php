<?php

namespace Unusualify\Modularity\Entities;

use Unusualify\Modularity\Entities\Model;
use Modules\PressRelease\Entities\Report;

class Spreadsheet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'published',
        'content',
        'report_id', // Ensure report_id is fillable if needed
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = modularityConfig('tables.spreadsheets', 'modularity_spreadsheets');
        parent::__construct($attributes);
    }

    public function getTable()
    {
        return modularityConfig('tables.spreadsheets', 'modularity_spreads');
    }

    /**
     * Get the report that owns the spreadsheet.
     */
    // public function report()
    // {
    //     return $this->belongsTo(Report::class, 'report_id', 'id');
    // }
}
