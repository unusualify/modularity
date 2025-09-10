<?php

namespace Modules\SystemPayment\Entities;

use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;

class MyPayment extends \Modules\SystemPayment\Entities\Payment
{
    use ModelHelpers;

    protected $creatableClass = \Modules\SystemPayment\Entities\Payment::class;

    protected $filepondableClass = \Modules\SystemPayment\Entities\Payment::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'published',
    ];
}
