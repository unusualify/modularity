<?php

namespace Unusualify\Modularity\Tests\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasUuid;

class UuidColumnNotExistException extends Model{

    use HasUuid;

}
