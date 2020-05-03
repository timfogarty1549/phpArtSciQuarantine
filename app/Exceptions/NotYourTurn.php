<?php

namespace App\Exceptions;

class NotYourTurnException extends ArtScienceException
{
    protected $code = 200;
    protected $message = "Not Your Turn ";
    protected $tag = 'NOT_YOUR_TURN';
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->message . $message, $this->code, $previous);
    }
    
}