<?php

namespace App\Exceptions;

class NotHostException extends ArtScienceException
{
    protected $code = 200;
    protected $tag = 'NOT_HOST';
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
    
}