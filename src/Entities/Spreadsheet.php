<?php

namespace Unusualify\Modularity\Entities;

use Unusualify\Modularity\Entities\Model;



class Spreadsheet extends Model
{

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'published',
        'content'
	];

    public function __construct(array $attributes = [])
    {
        $this->table = unusualConfig('tables.spreadsheets', 'modularity_spreadsheets');
        parent::__construct($attributes);
    }


}
