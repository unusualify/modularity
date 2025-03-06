<?php

namespace Unusualify\Modularity\Tests\Traits\HasFiles;

use Unusualify\Modularity\Entities\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;

class FileModel extends Model{

    use HasFiles;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'filename',
        'size',
    ];

}
