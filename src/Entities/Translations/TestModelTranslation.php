<?php

namespace Unusualify\Modularity\Entities\Translations;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class TestModel extends Model
{
    use HasTranslation;
}
