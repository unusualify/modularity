<?php

namespace Unusualify\Modularity\Events;

use Illuminate\Queue\SerializesModels;

class ModularityUserRegistering
{
    use SerializesModels;

    public function __construct(public $request) {}
}
