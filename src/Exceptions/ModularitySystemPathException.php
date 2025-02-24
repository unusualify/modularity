<?php

namespace Unusualify\Modularity\Exceptions;

use Exception;

class ModularitySystemPathException extends Exception
{
    public function __construct($message = "You cannot set system modules path in production", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
