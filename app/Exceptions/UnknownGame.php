<?php

namespace App\Exceptions;

use Exception;

class UnknownGameException extends Exception
{
    protected $code = 401;
    protected $message = "Unknown Game";
}