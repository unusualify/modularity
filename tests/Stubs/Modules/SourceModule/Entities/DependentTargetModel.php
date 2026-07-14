<?php

namespace Modules\SourceModule\Entities;

use Illuminate\Database\Eloquent\Model;

class DependentTargetModel extends Model
{
    protected $table = 'dependent_target_models';

    public $timestamps = false;
}
