<?php

namespace Unusualify\Modularity\Tests\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Unusualify\Modularity\Http\Controllers\Traits\Utilities\SendsEmailVerificationRegister;

class ControllerUsingSendsEmailVerificationRegister extends Controller
{
    use AuthorizesRequests, ValidatesRequests, SendsEmailVerificationRegister;
}
