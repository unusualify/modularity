<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
        'id',
        'authorized_id',
        'authorized_type',
        'authorizable_id',
        'authorizable_type',
	];

    public function authorized()
    {
        return $this->morphTo();
    }

    public function authorizable()
    {
        return $this->morphTo();
    }

    public function getTable()
    {
        return modularityConfig('tables.authorizations', 'modularity_authorizations');
    }

}
