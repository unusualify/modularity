<?php

namespace Unusualify\Modularity\Tests\Traits\HasPosition;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasPosition;

class PositionModel extends Model
{
    use HasPosition;

    protected $fillable = ['position'];
}
