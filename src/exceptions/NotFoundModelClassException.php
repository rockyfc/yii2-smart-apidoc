<?php

namespace smart\apidoc\exceptions;

use Throwable;

class NotFoundModelClassException extends DocException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}