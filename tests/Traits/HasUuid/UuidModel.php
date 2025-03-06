<?php

namespace Unusualify\Modularity\Tests\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasUuid;

class UuidModel extends Model
{
    use HasUuid;

    public static $uuidColumn = 'id';

}
