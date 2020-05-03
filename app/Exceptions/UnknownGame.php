<?php

namespace App\Exceptions;

class UnknownGameException extends ArtScienceException
{
    protected $code = 200;
    protected $message = "Unknown game ";
    protected $tag = "UNKNOWN_GAME ";
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->message . $message, $code, $previous);
    }
}