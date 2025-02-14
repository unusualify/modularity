<?php

namespace Unusualify\Modularity\Tests\Traits\HasSpreadable;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;

class SpreadModel extends Model
{
    use HasSpreadable;

    // Allow mass assignment of the database column and the _spread attribute.
    protected $fillable = ['title', '_spread'];
    protected $table = 'spreadable_models';
}
