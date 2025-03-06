<?php

namespace Unusualify\Modularity\Tests\Traits\HasPriceable;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasPriceable;


class TestModel extends Model
{
    use HasPriceable;

    protected $guarded = [];
    protected $table = 'test_models';
}
