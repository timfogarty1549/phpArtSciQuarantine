<?php

namespace App\Exceptions;

class PlayerNotFoundException extends ArtScienceException
{
    protected $code = 200;
    protected $message = "Player Not Found ";
    protected $tag = 'PLAYER_NOT_FOUND';
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->message . $message, $this->code, $previous);
    }
    
}